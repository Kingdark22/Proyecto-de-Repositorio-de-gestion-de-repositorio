<?php

namespace App\Services;

use App\Models\GrupoProyectoModulo;
use App\Models\Proyecto;
use App\Models\User;
use App\Repositories\GrupoProyectoRepository;
use App\Repositories\ProyectoRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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

        if ($isCoordinator) {
            // Coordinador: notificaciones administrativas generales
            $proyectosCompletados = Proyecto::where('pry_estado', 'Pendiente')->count();
            if ($proyectosCompletados > 0) {
                $notificaciones[] = [
                    'type' => 'warning',
                    'title' => 'Proyectos pendientes de revisión',
                    'mensaje' => "Hay {$proyectosCompletados} proyecto(s) completado(s) pendiente(s) de revisión por los profesores.",
                    'url' => route('proyectos.gestion'),
                ];
            }
        }

        if ($isTeacher) {
            $query = Proyecto::whereIn('pry_estado', ['Pendiente']);
            $query2 = Proyecto::where('pry_estado', 'Pendiente')->where('actualizado_por_estudiante', true);

            $cedula = trim($user->usu_cedula);

            $gruposCreados = app(GrupoProyectoService::class)->listar(['creador' => $cedula]);
                $clavesCreador = $gruposCreados->pluck('clave')->filter()->values()->toArray();

                if ($clavesCreador !== []) {
                    $query->whereIn('pry_direccion_logica', $clavesCreador);
                    $query2->whereIn('pry_direccion_logica', $clavesCreador);
                } else {
                    // Sin grupos creados: no mostrar proyectos ajenos,
                    // pero continuar para que las notificaciones por sección se ejecuten
                    $query->whereRaw('1 = 0');
                    $query2->whereRaw('1 = 0');
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
                    'title' => 'Componentes subidos',
                    'mensaje' => 'Los estudiantes subieron documentos en: ' . $p->titulo . '. Revise y apruebe los componentes.',
                    'url' => route('proyectos.gestion.edit', $p->id),
                    'proyecto_id' => $p->id,
                ];
            }
        }

        // ─── Notificaciones por sección: estudiantes sin equipo (solo profesor proyecto) ───
        if ($isTeacher) {
            try {
                $profesorSvc = app(\App\Services\IntranetProfessorService::class);
                $lapCodigo = $profesorSvc->lapsoVigenteCodigo();
                if ($lapCodigo) {
                    $equipoSvc = app(\App\Services\IntranetEquipoSeccionService::class);
                    $cedula = trim($user->usu_cedula);

                    $secCodigos = $profesorSvc->seccionesDelDocente($cedula, $lapCodigo);
                    $seccionesInfo = $equipoSvc->seccionesEnLapso($lapCodigo)
                        ->whereIn('sec_codigo', $secCodigos)
                        ->keyBy('sec_codigo');

                    if (!empty($secCodigos)) {
                        $cedulasEnGrupos = $this->grupoRepo->cedulasOcupadasEnLapso($lapCodigo);
                        $cedulasEnGruposIndex = array_flip($cedulasEnGrupos);

                        foreach ($secCodigos as $sec) {
                            $clave = $equipoSvc->construirClave($lapCodigo, $sec);
                            $integrantes = $equipoSvc->integrantes($clave);
                            $sinGrupo = [];

                            foreach ($integrantes as $est) {
                                $c = trim($est->cedula ?? '');
                                if ($c !== '' && !isset($cedulasEnGruposIndex[$c])) {
                                    $sinGrupo[$c] = true;
                                }
                            }

                            $totalSinGrupo = count($sinGrupo);
                            if ($totalSinGrupo > 0) {
                                $secInfo = $seccionesInfo->get($sec);
                                $secNombre = $secInfo->sec_nombre ?? "Sección {$sec}";
                                $plural = $totalSinGrupo > 1 ? 'n' : '';
                                $sEst = $totalSinGrupo > 1 ? 's' : '';
                                $notificaciones[] = [
                                    'type' => 'info',
                                    'title' => "Estudiantes sin equipo — Sección {$secNombre}",
                                    'mensaje' => "Falta{$plural} {$totalSinGrupo} estudiante{$sEst} de la sección {$secNombre} por integrar a un equipo de proyecto.",
                                    'url' => route('grupos-proyecto.create'),
                                ];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Error notificando estudiantes sin grupo: ' . $e->getMessage());
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

            // 1. Proyectos nuevos — notificación de proyecto creado desde grupo + subir documentos
            $proyectosNuevos = $this->proyectoRepo->pendientesEstudiante();

            foreach ($proyectosNuevos as $p) {
                if ($this->esMiembroDelProyecto($p, $cedula, $gruposSvc, [])) {
                    $notificaciones[] = [
                        'type' => 'info',
                        'title' => 'Nuevo proyecto',
                        'mensaje' => 'Se ha creado un proyecto para tu equipo: ' . $p->titulo . '. Ingresa para completar los datos.',
                        'url' => route('proyectos.gestion.edit', $p->id),
                        'proyecto_id' => $p->id,
                    ];
                }
            }

            // 1.5 Proyectos completados — todos los documentos aceptados, pendiente de aprobación final
            $proyectosCompletados = Proyecto::where('pry_estado', 'Pendiente')->get();
            $gruposCacheComp = $this->precargarGruposProyecto($proyectosCompletados, $gruposSvc);
            foreach ($proyectosCompletados as $p) {
                if ($this->esMiembroDelProyecto($p, $cedula, $gruposSvc, $gruposCacheComp)) {
                    $notificaciones[] = [
                        'type' => 'info',
                        'title' => 'Proyecto completado',
                        'mensaje' => 'Tu proyecto "' . $p->titulo . '" ha sido completado. Todos los documentos fueron aceptados por el profesor. Espera la aprobación final.',
                        'url' => route('proyectos.gestion.edit', $p->id),
                        'proyecto_id' => $p->id,
                    ];
                }
            }

            // 2. Proyectos rechazados que necesitan correcciones
            $proyectosRechazados = $this->proyectoRepo->rechazados();
            
            // 2.5 Documentos rechazados que necesitan correcciones
            $documentosRechazados = \App\Models\ProyectoDocumento::with(['proyecto', 'componente'])
                ->where('pd_estado', 2)
                ->whereNotNull('pd_observacion')
                ->get();

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
                        'mensaje' => 'Revisión requerida para "' . $p->titulo . '". Revisar detalles.',
                        'url' => route('proyectos.gestion', ['edit' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                }
            }

            foreach ($documentosRechazados as $doc) {
                $p = $doc->proyecto;
                if ($p && $this->esMiembroDelProyecto($p, $cedula, $gruposSvc, $gruposCache)) {
                    $notificaciones[] = [
                        'type' => 'danger',
                        'title' => 'Documento rechazado',
                        'mensaje' => 'El documento "' . ($doc->componente->nombre ?? 'Desconocido') . '" del proyecto "' . $p->titulo . '" fue rechazado. Motivo: ' . $doc->pd_observacion,
                        'url' => route('proyectos.gestion', ['edit' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                }
            }

            // 2.75 Documentos aceptados (últimos 7 días)
            $documentosAceptados = \App\Models\ProyectoDocumento::with(['proyecto', 'componente'])
                ->where('pd_estado', 1)
                ->where('updated_at', '>=', now()->subDays(7))
                ->get();

            $proyectosAceptDocs = $documentosAceptados->map(fn($d) => $d->proyecto)->filter()->unique('id');
            $gruposCacheAcept = $this->precargarGruposProyecto($proyectosAceptDocs, $gruposSvc);
            foreach ($documentosAceptados as $doc) {
                $p = $doc->proyecto;
                if ($p && $this->esMiembroDelProyecto($p, $cedula, $gruposSvc, $gruposCacheAcept)) {
                    $notificaciones[] = [
                        'type' => 'success',
                        'title' => 'Documento aceptado',
                        'mensaje' => 'El documento "' . ($doc->componente->nombre ?? 'Desconocido') . '" del proyecto "' . $p->titulo . '" fue aceptado por el profesor.',
                        'url' => route('proyectos.gestion', ['edit' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                }
            }

            // 3. Proyectos aprobados — notificación de repositorio + solvencia
            $proyectosAprobados = Proyecto::where('pry_estado', 'Aprobado')->get();
            $gruposCacheAprob = $this->precargarGruposProyecto($proyectosAprobados, $gruposSvc);
            foreach ($proyectosAprobados as $p) {
                if ($this->esMiembroDelProyecto($p, $cedula, $gruposSvc, $gruposCacheAprob)) {
                    // Notificación: subido al repositorio
                    $notificaciones[] = [
                        'type' => 'success',
                        'title' => 'Repositorio',
                        'mensaje' => 'Tu proyecto "' . $p->titulo . '" ha sido subido al repositorio institucional exitosamente.',
                        'url' => route('proyectos.buscar'),
                        'proyecto_id' => $p->id,
                    ];
                    // Notificación: solvencia disponible
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

    public function correoProfesor(string $cedulaProfesor, string $subject, string $message, ?string $url = null, ?string $urlText = null): void
    {
        try {
            $cedula = trim($cedulaProfesor);
            $email = null;

            try {
                $email = \App\Helpers\DualDatabase::table('persona')
                    ->whereRaw('TRIM(per_cedula) = ?', [$cedula])
                    ->value('per_email');
            } catch (\Throwable) {
                // Intranet/simulación no disponible
            }

            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($email)->send(new \App\Mail\NotificacionProyecto($subject, $message, $url, $urlText));
            } else {
                Log::info("No se pudo enviar correo a profesor {$cedula}: email no encontrado en persona. Verifique que el docente tenga per_email en intranet.");
            }
        } catch (\Throwable $e) {
            Log::warning("Error enviando correo a profesor {$cedulaProfesor}: " . $e->getMessage());
        }
    }

    public function correoEstudiante(string $cedula, string $subject, string $message, ?string $url = null, ?string $urlText = null): void
    {
        try {
            $email = \App\Helpers\DualDatabase::table('persona')
                ->whereRaw('TRIM(per_cedula) = ?', [trim($cedula)])
                ->value('per_email');

            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($email)->send(new \App\Mail\NotificacionProyecto($subject, $message, $url, $urlText));
            }
        } catch (\Throwable $e) {
            Log::warning("Error enviando correo a estudiante {$cedula}: " . $e->getMessage());
        }
    }

    public function notificarActualizacionEstudiante(Proyecto $proyecto, string $cedulaProfesor): void
    {
        $url = route('proyectos.gestion.edit', $proyecto->id);
        $this->correoProfesor(
            $cedulaProfesor,
            'Estudiante subió componentes del proyecto',
            "Un integrante del equipo ha subido documentos al proyecto \"{$proyecto->titulo}\". Ingrese al sistema para revisar y aprobar/rechazar los componentes.",
            $url,
            'Revisar componentes'
        );
    }

    public function notificarDocumentoAceptado(Proyecto $proyecto, string $componenteNombre, array $cedulasEstudiantes): void
    {
        $url = route('proyectos.gestion.edit', $proyecto->id);
        foreach ($cedulasEstudiantes as $cedula) {
            $this->correoEstudiante(
                $cedula,
                'Documento aceptado',
                "El documento \"{$componenteNombre}\" del proyecto \"{$proyecto->titulo}\" ha sido aceptado por el profesor. Continúe con los siguientes pasos.",
                $url,
                'Ver proyecto'
            );
        }
    }

    public function notificarDocumentoRechazado(Proyecto $proyecto, string $componenteNombre, string $observacion, array $cedulasEstudiantes): void
    {
        $url = route('proyectos.gestion.edit', $proyecto->id);
        foreach ($cedulasEstudiantes as $cedula) {
            $this->correoEstudiante(
                $cedula,
                'Documento rechazado',
                "El documento \"{$componenteNombre}\" del proyecto \"{$proyecto->titulo}\" ha sido rechazado.\nMotivo: {$observacion}\nCorrija y vuelva a subir el documento.",
                $url,
                'Ver proyecto'
            );
        }
    }

    public function notificarProyectoRechazado(Proyecto $proyecto, string $motivo, array $cedulasEstudiantes): void
    {
        $url = route('proyectos.gestion.edit', $proyecto->id);
        foreach ($cedulasEstudiantes as $cedula) {
            $this->correoEstudiante(
                $cedula,
                'Proyecto rechazado — requiere correcciones',
                "El proyecto \"{$proyecto->titulo}\" ha sido rechazado.\n\nMotivo: {$motivo}\n\nIngrese al sistema para corregir y volver a enviar los documentos requeridos.",
                $url,
                'Ver proyecto'
            );
        }
    }

    public function notificarProyectoCompletado(Proyecto $proyecto, array $cedulasEstudiantes): void
    {
        $url = route('proyectos.gestion.edit', $proyecto->id);
        foreach ($cedulasEstudiantes as $cedula) {
            $this->correoEstudiante(
                $cedula,
                'Proyecto completado — pendiente de aprobación',
                "El proyecto \"{$proyecto->titulo}\" ha sido completado. Todos los documentos han sido aceptados por el profesor.\n\nAhora queda pendiente de la aprobación final. Puede consultar el estado en el sistema.",
                $url,
                'Ver proyecto'
            );
        }
    }

    public function notificarProyectoAprobado(Proyecto $proyecto, array $cedulasEstudiantes): void
    {
        $urlRepo = route('proyectos.buscar');
        $urlSolvencia = route('proyectos.gestion.solvencia', $proyecto->id);
        foreach ($cedulasEstudiantes as $cedula) {
            // Correo: subido al repositorio
            $this->correoEstudiante(
                $cedula,
                'Proyecto subido al Repositorio Institucional',
                "El proyecto \"{$proyecto->titulo}\" ha sido subido con éxito al Repositorio Institucional de la UPTP Juan de Jesús Montilla.\n\nYa puede consultarlo en el repositorio público y descargar su solvencia.",
                $urlRepo,
                'Ver en el repositorio'
            );
            // Correo: solvencia disponible
            $this->correoEstudiante(
                $cedula,
                'Solvencia disponible',
                "El proyecto \"{$proyecto->titulo}\" ha sido aprobado. Ya puede descargar su solvencia.",
                $urlSolvencia,
                'Descargar solvencia'
            );
        }
    }

    public function notificarNuevoGrupo(string $nombreGrupo, string $cedulaProfesor, array $cedulasEstudiantes): void
    {
        $url = route('grupos-proyecto.index');
        foreach ($cedulasEstudiantes as $cedula) {
            $this->correoEstudiante(
                $cedula,
                'Nuevo equipo de proyecto',
                "Has sido agregado al equipo \"{$nombreGrupo}\". Ingrese al sistema para más información.",
                $url,
                'Ver equipo'
            );
        }
    }

    public function notificarNuevoProyectoDesdeGrupo(Proyecto $proyecto, string $nombreGrupo, array $cedulasEstudiantes): void
    {
        $url = route('proyectos.gestion.edit', $proyecto->id);
        foreach ($cedulasEstudiantes as $cedula) {
            $this->correoEstudiante(
                $cedula,
                'Nuevo proyecto creado para tu equipo',
                "Se ha creado un nuevo proyecto para el equipo \"{$nombreGrupo}\".\n\nIngrese al sistema para completar los datos y subir los documentos requeridos.",
                $url,
                'Completar proyecto'
            );
        }
    }

    public function contarPendientes(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        $cacheKey = 'notif_count_' . trim((string) $user->usu_cedula) . '_' . session('active_role', 'none');

        return (int) Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            return count($this->listar($user));
        });
    }

    /**
     * Invalida el cache del conteo de notificaciones para un usuario.
     * Llamar cuando cambia el estado de un proyecto o documento.
     */
    public function invalidarCache(User $user): void
    {
        $cacheKey = 'notif_count_' . trim((string) $user->usu_cedula) . '_' . session('active_role', 'none');
        Cache::forget($cacheKey);
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
            $grupo = GrupoProyectoModulo::porIdentificador($clave);
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
