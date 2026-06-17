<?php

namespace App\Services;

use App\Models\GrupoProyectoModulo;
use App\Models\Proyecto;
use App\Models\User;

class NotificacionService
{
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
            $query = Proyecto::whereIn('estado_validacion', ['pendiente', 'completado']);

            if ($isTeacher) {
                $clavesDocente = app(ProyectoGestionService::class)->clavesEquipoFiltroValidacion($user);
                if ($clavesDocente !== null && $clavesDocente !== []) {
                    // Separar claves EQSEC y buscar EQGRP relacionados via contexto JSONB (solo una consulta)
                    $clavesEqsec = array_filter($clavesDocente, fn($c) => str_starts_with($c, \App\Services\IntranetEquipoSeccionService::PREFIJO_REF . ':'));

                    $gruposProfesor = [];
                    if ($clavesEqsec !== []) {
                        $equipoService = app(\App\Services\IntranetEquipoSeccionService::class);
                        $gruposProfesor = GrupoProyectoModulo::where(function ($q) use ($clavesEqsec, $equipoService) {
                            foreach ($clavesEqsec as $clave) {
                                $partes = $equipoService->parsearClave($clave);
                                if ($partes) {
                                    $q->orWhereRaw(
                                        "CAST(grp_contexto AS jsonb)->>'lap_codigo' = ? AND CAST(grp_contexto AS jsonb)->>'sec_codigo' = ?",
                                        [(string) $partes['lap_codigo'], (string) $partes['sec_codigo']]
                                    );
                                }
                            }
                        })->pluck('grp_codigo')
                            ->map(fn($id) => \App\Services\GrupoProyectoService::PREFIJO . ':' . $id)
                            ->toArray();
                    }

                    $query->where(function ($q) use ($clavesDocente, $gruposProfesor) {
                        $q->whereIn('pry_direccion_logica', $clavesDocente);
                        if ($gruposProfesor) {
                            $q->orWhereIn('pry_direccion_logica', $gruposProfesor);
                        }
                    });
                }
            }

            $proyectos = $query->get();

            foreach ($proyectos as $p) {
                if ($p->actualizado_por_estudiante) {
                    $notificaciones[] = [
                        'type' => 'info',
                        'title' => 'Proyecto actualizado',
                        'mensaje' => 'Proyecto actualizado por el líder: ' . $p->titulo,
                        'url' => route('proyectos.gestion', ['details' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                } else {
                    $notificaciones[] = [
                        'type' => 'info',
                        'title' => 'Proyecto registrado',
                        'mensaje' => 'Nuevo proyecto registrado: ' . $p->titulo,
                        'url' => route('proyectos.gestion', ['details' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                }
            }
        } elseif ($isStudent) {
            $cedula = trim($user->usu_cedula);
            $gruposSvc = app(GrupoProyectoService::class);

            // 0. Notificar si el estudiante fue agregado a un equipo sin proyecto aún
            try {
                $gruposEstudiante = GrupoProyectoModulo::whereRaw(
                    "CAST(grp_miembros AS jsonb) @> ?",
                    ['[{"cedula":"' . $cedula . '"}]']
                )->get();

                if ($gruposEstudiante->isNotEmpty()) {
                    $clavesGrupos = $gruposEstudiante->pluck('grp_codigo')
                        ->map(fn($id) => GrupoProyectoService::PREFIJO . ':' . $id)
                        ->toArray();

                    $proyectosExistentes = Proyecto::whereNotNull('pry_direccion_logica')
                        ->whereIn('pry_direccion_logica', $clavesGrupos)
                        ->pluck('pry_direccion_logica')
                        ->toArray();

                    foreach ($gruposEstudiante as $g) {
                        $clave = GrupoProyectoService::PREFIJO . ':' . $g->grp_codigo;
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
            $proyectosNuevos = Proyecto::where('actualizado_por_estudiante', false)
                ->where('estado_validacion', '!=', 'aprobado')
                ->where('estado_validacion', '!=', 'rechazado')
                ->whereNotNull('pry_direccion_logica')
                ->get();

            // 2. Proyectos rechazados que necesitan correcciones
            $proyectosRechazados = Proyecto::where('estado_validacion', 'rechazado')
                ->whereNotNull('pry_direccion_logica')
                ->get();

            foreach ($proyectosNuevos as $p) {
                if ($this->esLiderDelProyecto($p, $cedula, $gruposSvc)) {
                    $notificaciones[] = [
                        'type' => 'warning',
                        'title' => 'Subir documentos',
                        'mensaje' => 'Has sido seleccionado como líder del proyecto. Sube los documentos: ' . $p->titulo,
                        'url' => route('proyectos.gestion', ['edit' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                }
            }

            foreach ($proyectosRechazados as $p) {
                if ($this->esLiderDelProyecto($p, $cedula, $gruposSvc)) {
                    $notificaciones[] = [
                        'type' => 'warning',
                        'title' => 'Proyecto rechazado',
                        'mensaje' => 'Revisión requerida para "' . $p->titulo . '". Motivo: ' . ($p->motivo_rechazo ?: 'Revisar detalles.'),
                        'url' => route('proyectos.gestion', ['edit' => $p->id]),
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

    protected function esLiderDelProyecto(Proyecto $p, string $cedula, GrupoProyectoService $gruposSvc): bool
    {
        $clave = $p->equipo_ref;
        if ($clave === '') {
            return false;
        }

        $partes = $gruposSvc->parsearClave($clave);
        if (!$partes || ($partes['tipo'] ?? '') !== GrupoProyectoService::PREFIJO) {
            return false;
        }

        $grupo = GrupoProyectoModulo::find($partes['grp_codigo'] ?? 0);
        if (!$grupo) {
            return false;
        }

        $miembros = $grupo->grp_miembros ?? [];
        foreach ($miembros as $m) {
            if (trim($m['cedula'] ?? '') === $cedula
                && (int) ($m['rol_id'] ?? 0) === IntranetEquipoSeccionService::ROL_LIDER
            ) {
                return true;
            }
        }

        return false;
    }
}
