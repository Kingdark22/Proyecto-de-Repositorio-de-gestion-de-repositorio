<?php

namespace App\Services;

use App\Models\GrupoProyectoModulo;
use App\Models\Proyecto;
use App\Models\User;
use App\Repositories\GrupoProyectoRepository;
use App\Repositories\ProyectoRepository;

class NotificacionService
{
    public function __construct(
        protected ProyectoRepository $proyectoRepo,
        protected GrupoProyectoRepository $grupoRepo,
    ) {}

    public function listar(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $userRoleService = app(UserRoleService::class);
        $activeRole = $userRoleService->getActiveRole($user);
        $isAdmin = $activeRole === 'administrador';
        $isCoordinator = $activeRole === 'coordinador';
        $isTeacher = $activeRole === 'profesor proyecto';
        $isStudent = $activeRole === 'estudiante';

        $notificaciones = [];

        if ($isAdmin || $isCoordinator || $isTeacher) {
            $query = Proyecto::whereIn('estado_validacion', ['completado']);
            $query2 = Proyecto::where('estado_validacion', 'pendiente')->where('actualizado_por_estudiante', true);

            if ($isTeacher) {
                $cedula = trim($user->usu_cedula);
                $gruposCreados = app(GrupoProyectoService::class)->listar(['creador' => $cedula]);
                $clavesCreador = $gruposCreados->pluck('clave')->filter()->values()->toArray();

                if ($clavesCreador !== []) {
                    $query->whereIn('pry_direccion_logica', $clavesCreador);
                    $query2->whereIn('pry_direccion_logica', $clavesCreador);
                } else {
                    return [];
                }
            }

            $proyectos = $query->get();
            $proyectosActualizados = $query2->get();

            foreach ($proyectos as $p) {
                $notificaciones[] = [
                    'type' => 'warning',
                    'title' => 'Pendiente de revisión',
                    'mensaje' => $p->titulo,
                    'url' => route('proyectos.gestion.edit', $p->id),
                    'proyecto_id' => $p->id,
                ];
            }

            foreach ($proyectosActualizados as $p) {
                $notificaciones[] = [
                    'type' => 'info',
                    'title' => 'Actualizado por el líder',
                    'mensaje' => $p->titulo,
                    'url' => route('proyectos.gestion.edit', $p->id),
                    'proyecto_id' => $p->id,
                ];
            }
        } elseif ($isStudent) {
            $cedula = trim($user->usu_cedula);
            $gruposSvc = app(GrupoProyectoService::class);

            // 0. Notificar si el estudiante fue agregado a un equipo sin proyecto aún
            try {
                $gruposEstudiante = $this->grupoRepo->findByMiembroCedula($cedula);

                if ($gruposEstudiante->isNotEmpty()) {
                    $clavesGrupos = $gruposEstudiante->map(fn($g) => $g->grp_identificador ?: (GrupoProyectoService::PREFIJO . ':' . $g->grp_codigo))->toArray();

                    $proyectosExistentes = $this->proyectoRepo->conEquipoRefNotNull()
                        ->whereIn('pry_direccion_logica', $clavesGrupos)
                        ->pluck('pry_direccion_logica')
                        ->toArray();

                    foreach ($gruposEstudiante as $g) {
                        $clave = $g->grp_identificador ?: (GrupoProyectoService::PREFIJO . ':' . $g->grp_codigo);
                        if (!in_array($clave, $proyectosExistentes, true)) {
                            $notificaciones[] = [
                                'type' => 'info',
                                'title' => 'Equipo de proyecto',
                                'mensaje' => 'Has sido agregado al equipo: ' . $g->grp_nombre,
                                'url' => route('grupos-proyecto.index'),
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Error notificando equipo de proyecto: ' . $e->getMessage());
            }

            // 1. Proyectos nuevos que necesitan subir documentos
            $proyectosNuevos = $this->proyectoRepo->pendientesEstudiante();

            // 2. Proyectos rechazados que necesitan correcciones
            $proyectosRechazados = $this->proyectoRepo->rechazados();

            // Precargar todos los grupos en UNA consulta para evitar N+1
            $gruposCache = $this->precargarGruposProyecto(
                $proyectosNuevos->merge($proyectosRechazados),
                $gruposSvc
            );

            foreach ($proyectosNuevos as $p) {
                if ($this->esMiembroDelProyecto($p, $cedula, $gruposSvc, $gruposCache)) {
                    $notificaciones[] = [
                        'type' => 'warning',
                        'title' => 'Subir documentos',
                        'mensaje' => 'Eres miembro del equipo. Sube los documentos del proyecto: ' . $p->titulo,
                        'url' => route('proyectos.gestion', ['edit' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                }
            }

            foreach ($proyectosRechazados as $p) {
                if ($this->esMiembroDelProyecto($p, $cedula, $gruposSvc, $gruposCache)) {
                    $notificaciones[] = [
                        'type' => 'warning',
                        'title' => 'Proyecto rechazado',
                        'mensaje' => 'Revisión requerida para "' . $p->titulo . '". Motivo: ' . ($p->motivo_rechazo ?: 'Revisar detalles.'),
                        'url' => route('proyectos.gestion', ['edit' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                }
            }

            // 3. Proyectos aprobados con solvencia disponible
            $proyectosAprobados = Proyecto::where('estado_validacion', 'aprobado')->where('estado_logico', true)->get();
            $gruposCacheAprob = $this->precargarGruposProyecto($proyectosAprobados, $gruposSvc);
            foreach ($proyectosAprobados as $p) {
                if ($this->esMiembroDelProyecto($p, $cedula, $gruposSvc, $gruposCacheAprob)) {
                    $notificaciones[] = [
                        'type' => 'success',
                        'title' => 'Solvencia disponible',
                        'mensaje' => 'Tu proyecto "' . $p->titulo . '" ha sido aprobado. Ya puedes descargar la solvencia.',
                        'url' => route('proyectos.gestion.solvencia', $p->id),
                        'proyecto_id' => $p->id,
                    ];
                }
            }
        }

        return $notificaciones;
    }

    public function contarPendientes(?User $user): int
    {
        return count($this->listar($user));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Proyecto>  $proyectos
     * @return array<int, GrupoProyectoModulo|null>  grp_codigo => model
     */
    protected function precargarGruposProyecto(\Illuminate\Support\Collection $proyectos, GrupoProyectoService $gruposSvc): array
    {
        $gruposCache = [];
        $codigos = [];

        $identificadores = [];
        $codigos = [];
        foreach ($proyectos as $p) {
            $clave = $p->equipo_ref;
            if ($clave === '') {
                continue;
            }
            if (str_starts_with($clave, 'EQSEC:')) {
                $partes = $gruposSvc->parsearClave($clave);
                if ($partes && ($partes['tipo'] ?? '') === 'EQSEC') {
                    $codigos[] = (int) ($partes['sec_codigo'] ?? 0);
                }
            } elseif (str_starts_with($clave, 'EQGRP:')) {
                $partes = $gruposSvc->parsearClave($clave);
                if ($partes && ($partes['tipo'] ?? '') === GrupoProyectoService::PREFIJO) {
                    $codigos[] = (int) ($partes['grp_codigo'] ?? 0);
                }
            } else {
                $identificadores[] = $clave;
            }
        }

        $codigos = array_unique(array_filter($codigos));
        $grupos = collect();
        if ($codigos) {
            $grupos = GrupoProyectoModulo::whereIn('grp_codigo', $codigos)->get()->keyBy('grp_codigo');
        }
        if ($identificadores) {
            $gruposPorIdent = GrupoProyectoModulo::whereIn('grp_identificador', $identificadores)->get()->keyBy('grp_identificador');
            foreach ($gruposPorIdent as $g) {
                $codigos[] = $g->grp_codigo;
                $grupos[$g->grp_codigo] = $g;
            }
        }

        foreach ($codigos as $cod) {
            $gruposCache[$cod] = $grupos->get($cod);
        }

        return $gruposCache;
    }

    /**
     * @param  array<int, GrupoProyectoModulo|null>  $gruposCache
     */
    protected function esMiembroDelProyecto(Proyecto $p, string $cedula, GrupoProyectoService $gruposSvc, array $gruposCache = []): bool
    {
        $clave = $p->equipo_ref;
        if ($clave === '') {
            return false;
        }

        $grupo = null;

        // Try as identificador first
        if (!str_starts_with($clave, 'EQGRP:') && !str_starts_with($clave, 'EQSEC:')) {
            $grupo = GrupoProyectoModulo::where('grp_identificador', $clave)->first();
        }

        if (!$grupo) {
            $partes = $gruposSvc->parsearClave($clave);
            if ($partes && ($partes['tipo'] ?? '') === GrupoProyectoService::PREFIJO) {
                $codigo = (int) ($partes['grp_codigo'] ?? 0);
                $grupo = $gruposCache[$codigo] ?? null;
                if (!$grupo) {
                    $grupo = GrupoProyectoModulo::find($codigo);
                }
            }
        }

        if (!$grupo) {
            return false;
        }

        $miembros = $grupo->grp_miembros ?? [];
        foreach ($miembros as $m) {
            if (trim($m['cedula'] ?? '') === $cedula) {
                return true;
            }
        }

        return false;
    }
}
