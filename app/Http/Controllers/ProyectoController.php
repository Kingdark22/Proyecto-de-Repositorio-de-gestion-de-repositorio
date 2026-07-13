<?php

namespace App\Http\Controllers;

use App\Models\GrupoProyectoModulo;
use App\Models\Proyecto;
use App\Services\NotificacionService;
use App\Services\ProyectoGestionService;
use App\Services\GrupoProyectoService;
use App\Services\IntranetEquipoSeccionService;
use App\Services\ReporteDepositoService;
use App\Services\SpreadsheetMlWriter;
use App\Services\UserRoleService;
use App\Repositories\CatalogoRepository;
use App\Repositories\ComunidadRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProyectoController extends Controller
{
    public function __construct(
        protected ProyectoGestionService $gestion,
        protected GrupoProyectoService $grupos,
        protected IntranetEquipoSeccionService $equipoSeccion,
        protected ReporteDepositoService $reporteDeposito,
        protected UserRoleService $userRoleService,
        protected NotificacionService $notificacionService,
        protected CatalogoRepository $catalogoRepo,
        protected ComunidadRepository $comunidadRepo,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $activeRole = $this->userRoleService->getActiveRole($user);
        $esProfesor = $this->userRoleService->roleMatches('profesor proyecto', $activeRole);
        $esGestionador = $this->userRoleService->roleMatches('gestionador', $activeRole);
        $esAdmin = $this->userRoleService->roleMatches('administrador', $activeRole);
        $esCoordinador = $this->userRoleService->roleMatches('coordinador', $activeRole);

        $search = $request->get('search', '');
        $filterEstado = $request->get('estado', '');
        $filterComunidad = $request->get('comunidad', '');
        $filterLapso = $request->get('lapso', '');
        $page = (int) $request->get('page', 1);

        // Grupos del docente (solo profesor proyecto)
        $gruposDocente = [];
        if ($esProfesor) {
            $filtrosGrupos = [];
            if ($filterLapso !== '') $filtrosGrupos['lapso'] = (int) $filterLapso;
            if (!in_array($activeRole, ['profesor proyecto'], true)) {
                $filterPrograma = $request->get('programa', '');
                $filterTrayecto = $request->get('trayecto', '');
                if ($filterPrograma !== '') $filtrosGrupos['programa'] = (int) $filterPrograma;
                if ($filterTrayecto !== '') $filtrosGrupos['trayecto'] = $filterTrayecto;
            }
            $gruposDocente = $this->gestion->gruposDelDocente($user, $filtrosGrupos)->toArray();
        }

        // Proyectos líder (estudiante)
        $esEstudianteLider = false;
        $proyectosLider = collect();
        if ($user && !$esProfesor) {
            if (!$this->userRoleService->roleMatches('administrador', $activeRole)
                && !$this->userRoleService->roleMatches('coordinador', $activeRole)
                && !$this->userRoleService->roleMatches('gestionador', $activeRole)) {
                $esEstudianteLider = true;
                $proyectosLider = $this->gestion->proyectosLider($user);
            }
        }

        // Listado general (todos menos estudiantes líder)
        $datosListado = [];
        $mostrarListado = !$esEstudianteLider;
        if ($mostrarListado) {
            $datosListado = $this->gestion->datosVistaListado([
                'search' => $search,
                'estado' => $filterEstado,
                'comunidad' => $filterComunidad,
                'lapso' => $filterLapso,
            ], $page, $user);
        }

        // Catálogos para filtros de grupos
        $lapsosFiltro = Cache::remember(
            'proyecto_manager_lapsos',
            now()->addMinutes(10),
            fn() => \App\Models\LapsoAcademico::activos()->orderByDesc('lap_codigo')->get()
        );

        $programasFiltro = collect();
        $trayectosFiltro = collect();
        $lapsoFiltro = $filterLapso !== '' ? (int) $filterLapso : null;
        if ($lapsoFiltro) {
            $programasFiltro = $this->equipoSeccion->programasEnLapso($lapsoFiltro);
        }

        // Catálogos para filtros del modal de exportación
        $catTtl = now()->addMinutes(10);
        $comunidadesFiltro = Cache::remember('export_comunidades', $catTtl, fn() =>
            \App\Models\Comunidad::orderBy('nombre')->get(['com_codigo', 'com_nombre'])
        );
        $lineasFiltro = Cache::remember('export_lineas', $catTtl, fn() =>
            \App\Models\LineaInvestigacion::where('activo', true)->orderBy('nombre_investigacion')->get()
        );
        $tiposInvFiltro = Cache::remember('export_tipos_investigacion', $catTtl, fn() =>
            \App\Models\TipoInvestigacion::where('estado_logico', true)->orderBy('nombre')->get()
        );
        $metodologiasFiltro = Cache::remember('export_metodologias', $catTtl, fn() =>
            \App\Models\MetodologiaInvestigacion::where('estado_logico', true)->orderBy('nombre')->get()
        );

        $canValidate = $user ? $this->gestion->usuarioPuedeValidar($user) : false;
        $proyectosLiderIds = $this->gestion->proyectosDondeEsMiembro($user);

        $proyectosConDocumentosRechazados = [];
        if ($canValidate) {
            $proyectosConDocumentosRechazados = \Illuminate\Support\Facades\Cache::remember('proyectos_con_docs_rechazados', 60, function () {
                return \App\Models\ProyectoDocumento::where('pd_estado', 2)
                    ->distinct()
                    ->pluck('pry_codigo')
                    ->toArray();
            });
        }

        if ($request->ajax() && $request->get('ajax_listado')) {
            $html = view('proyectos._listado_tabla', compact(
                'datosListado', 'canValidate', 'esAdmin', 'esCoordinador', 'esGestionador',
                'proyectosConDocumentosRechazados'
            ))->render();
            $paginacion = ($datosListado['proyectos'] ?? collect())->links()->render();
            return response()->json(['html' => $html, 'paginacion' => $paginacion]);
        }

        return view('proyectos.index', compact(
            'search', 'filterEstado', 'filterComunidad', 'filterLapso',
            'esProfesor', 'esAdmin', 'esGestionador', 'esCoordinador', 'esEstudianteLider',
            'gruposDocente', 'proyectosLider', 'proyectosLiderIds',
            'datosListado', 'mostrarListado', 'canValidate',
            'lapsosFiltro', 'programasFiltro', 'trayectosFiltro',
            'proyectosConDocumentosRechazados',
            'comunidadesFiltro', 'lineasFiltro', 'tiposInvFiltro', 'metodologiasFiltro',

        ));
    }

    public function edit($id)
    {
        $user = auth()->user();
        $activeRole = $this->userRoleService->getActiveRole($user);
        $esProfesor = $this->userRoleService->roleMatches('profesor proyecto', $activeRole);
        $esGestionador = $this->userRoleService->roleMatches('gestionador', $activeRole);
        $esAdmin = $this->userRoleService->roleMatches('administrador', $activeRole);
        $esCoordinador = $this->userRoleService->roleMatches('coordinador', $activeRole);

        $proyecto = Proyecto::findOrFail($id);
        $esMiembro = $this->gestion->usuarioEsMiembroDelProyecto($user, $proyecto);
        $esAdminEnSistema = $this->gestion->usuarioEsAdminEnSistema($user);

        // Verificar si el usuario es el profesor proyecto creador
        $esProfesorCreador = false;
        $usuarioCedula = trim((string) $user->usu_cedula);
        $usuarioUsuNombre = trim((string) $user->usu_nombre);
        $clave = $proyecto->equipo_ref ?? '';
        if ($clave !== '' && $esProfesor) {
            $grupo = GrupoProyectoModulo::porIdentificador($clave);
            if ($grupo) {
                $ctx = $grupo->grp_contexto;
                $creadorUsu = trim((string) ($ctx['creador_usuario'] ?? ''));
                $creadorCed = trim((string) ($ctx['creador_cedula'] ?? $grupo->grp_creador_cedula ?? ''));
                // 1) Por usu_nombre (nuevo)
                $matchUsu = $creadorUsu !== '' && $creadorUsu === $usuarioUsuNombre;
                // 2) Fallback por cédula (legacy)
                $matchCed = !$matchUsu && $creadorCed !== '' && $creadorCed === $usuarioCedula;
                $esProfesorCreador = $matchUsu || $matchCed;
            }
        }

        // Solo integrantes del equipo, profesor proyecto creador, coordinador o administrador pueden acceder
        if (!$esMiembro && !$esProfesorCreador && !$esCoordinador && !$esAdmin) {
            return redirect()->route('proyectos.gestion')
                ->with('error', 'No tienes permiso para acceder a este proyecto.');
        }

        $modoActualizacion = $esMiembro && !$esAdminEnSistema;
        $canValidate = $esProfesorCreador;
        $soloLectura = $esCoordinador || $esAdmin;

        $datosForm = $this->gestion->cargarParaEdicion($id);
        $estadoForm = $this->buildEstadoFromDatos($datosForm);
        $catalogosForm = $this->gestion->datosVistaFormulario($estadoForm);

        // Involucrados
        $involucradosProyecto = $this->gestion->involucradosDelProyecto($id)->toArray();

        // Miembros del grupo
        $miembrosGrupo = [];
        $clave = $datosForm['equipo_seccion_clave'] ?? '';
        if ($clave !== '') {
            $this->cargarMiembrosGrupo($clave, $miembrosGrupo);
        }

        return view('proyectos.registro', compact(
            'proyecto', 'datosForm', 'catalogosForm',
            'esProfesor', 'esGestionador', 'modoActualizacion',
            'involucradosProyecto', 'miembrosGrupo', 'clave',
            'canValidate', 'esAdmin', 'esMiembro', 'soloLectura', 'esProfesorCreador', 'esCoordinador',
        ));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $proyecto = Proyecto::findOrFail($id);

        $activeRole = $this->userRoleService->getActiveRole($user);
        $esProfesor = $this->userRoleService->roleMatches('profesor proyecto', $activeRole);
        $esAdmin = $this->userRoleService->roleMatches('administrador', $activeRole);
        $esCoordinador = $this->userRoleService->roleMatches('coordinador', $activeRole);

        $esMiembro = $this->gestion->usuarioEsMiembroDelProyecto($user, $proyecto);
        $esAdminEnSistema = $this->gestion->usuarioEsAdminEnSistema($user);

        // Verificar si el usuario es el profesor proyecto creador
        $esProfesorCreador = false;
        $usuarioCedula = trim((string) $user->usu_cedula);
        $usuarioUsuNombre = trim((string) $user->usu_nombre);
        $clave = $proyecto->equipo_ref ?? '';
        if ($clave !== '' && $esProfesor) {
            $grupo = GrupoProyectoModulo::porIdentificador($clave);
            if ($grupo) {
                $ctx = $grupo->grp_contexto;
                $creadorUsu = trim((string) ($ctx['creador_usuario'] ?? ''));
                $creadorCed = trim((string) ($ctx['creador_cedula'] ?? $grupo->grp_creador_cedula ?? ''));
                // 1) Por usu_nombre (nuevo)
                $matchUsu = $creadorUsu !== '' && $creadorUsu === $usuarioUsuNombre;
                // 2) Fallback por cédula (legacy)
                $matchCed = !$matchUsu && $creadorCed !== '' && $creadorCed === $usuarioCedula;
                $esProfesorCreador = $matchUsu || $matchCed;
            }
        }

        // Admin y coordinador no pueden modificar proyectos
        if ($esAdmin || $esCoordinador) {
            return redirect()->route('proyectos.gestion')
                ->with('error', 'No tienes permiso para modificar este proyecto.');
        }

        // Solo integrantes del equipo y profesor proyecto creador pueden actualizar
        if (!$esMiembro && !$esProfesorCreador) {
            return redirect()->route('proyectos.gestion')
                ->with('error', 'No tienes permiso para modificar este proyecto.');
        }

        $modoActualizacion = $esMiembro && !$esAdminEnSistema;

        $estadoForm = [
            'resumen' => $request->input('resumen', $proyecto->resumen ?? ''),
            'linea_investigacion_id' => $request->input('linea_investigacion_id', $proyecto->linea_investigacion_id),
            'metodologia_id' => $request->input('metodologia_id', $proyecto->metodologia_id),
            'tipo_investigacion_id' => $request->input('tipo_investigacion_id', $proyecto->tipo_investigacion_id),
            'objetivo_investigacion_id' => $request->input('objetivo_investigacion_id', $proyecto->objetivo_investigacion_id),
            'titulo' => $proyecto->titulo,
            'comunidad_id' => $request->input('comunidad_id', $proyecto->comunidad_id),
            'cantidad_beneficiados' => $request->input('cantidad_beneficiados', $proyecto->pry_cantidad_beneficiados),
            'equipo_seccion_clave' => $request->input('equipo_seccion_clave', $proyecto->equipo_ref),
            'filterLapsoEquipo' => $request->input('filterLapsoEquipo', ''),
            'filterProgramaEquipo' => $request->input('filterProgramaEquipo', ''),
            'filterSeccionEquipo' => $request->input('filterSeccionEquipo', ''),
            'programa_id' => $request->input('programa_id_derived'),
            'trayecto' => $request->input('trayecto_derived', ''),
            'trayecto_codigo' => $request->input('trayecto_derived_codigo', ''),
        ];

        if ($modoActualizacion) {
            // Validación base
            $request->validate([
                'documentos' => 'nullable|array',
            ], [], [
                'documentos' => 'documentos',
            ]);

            // Validación dinámica por componente (tipo, tamaño, obligatorios)
            $programaId = $estadoForm['programa_id'] ?? null;
            $trayectoCodigo = $estadoForm['trayecto_codigo'] ?? null;

            if ($programaId) {
                $componentes = $this->catalogoRepo->componentesPorProgramaYTrayecto($programaId, $trayectoCodigo);
                $existingDocs = $proyecto->documentos->keyBy('comp_codigo');

                $docRules = [];
                $docMessages = [];

                foreach ($componentes as $comp) {
                    $field = 'documentos.' . $comp->id;
                    $label = $comp->nombre;
                    $rules = [];

                    // OBLIGATORIO si el componente lo exige y no hay documento previo
                    if ($comp->es_obligatorio && !$existingDocs->has($comp->id)) {
                        $rules[] = 'required';
                    } else {
                        $rules[] = 'nullable';
                    }

                    $rules[] = 'file';

                    // VALIDAR TIPO DE ARCHIVO desde el componente
                    if ($comp->tipo_archivo) {
                        $mimeMap = [
                            'pdf' => 'pdf',
                            'zip' => 'zip',
                            'rar' => 'rar',
                            'doc' => 'doc,docx',
                            'docx' => 'doc,docx',
                            'xls' => 'xls,xlsx',
                            'xlsx' => 'xls,xlsx',
                            'img' => 'jpg,jpeg,png,gif',
                        ];
                        $tipos = explode(',', $comp->tipo_archivo);
                        $mimes = [];
                        foreach ($tipos as $t) {
                            $t = trim($t);
                            if (isset($mimeMap[$t])) {
                                $mimes[] = $mimeMap[$t];
                            }
                        }
                        if ($mimes) {
                            $rules[] = 'mimes:' . implode(',', $mimes);
                            $docMessages[$field . '.mimes'] = "El componente {$label} debe ser un archivo de tipo: " . implode(', ', $mimes) . '.';
                        }
                    }

                    // VALIDAR TAMAÑO MÁXIMO desde el componente
                    if ($comp->tamano_maximo_mb) {
                        $rules[] = 'max:' . ($comp->tamano_maximo_mb * 1024);
                        $docMessages[$field . '.max'] = "El componente {$label} no debe superar los {$comp->tamano_maximo_mb}MB.";
                    }

                    if ($comp->es_obligatorio && !$existingDocs->has($comp->id)) {
                        $docMessages[$field . '.required'] = "El componente {$label} es obligatorio.";
                    }

                    $docRules[$field] = $rules;
                }

                if ($docRules) {
                    $docAttributes = [];
                    foreach ($componentes as $comp) {
                        $docAttributes['documentos.' . $comp->id] = $comp->nombre;
                    }
                    $request->validate($docRules, $docMessages, $docAttributes);
                }
            }
        } else {
            $rules = $this->gestion->reglasValidacion($estadoForm, $user, true);
            $request->validate($rules, [
                'titulo.required' => 'El título del proyecto es obligatorio.',
                'resumen.required' => 'El resumen es obligatorio para los estudiantes.',
                'comunidad_id.required' => 'La comunidad es obligatoria.',
            ], [
                'titulo' => 'título del proyecto',
                'resumen' => 'resumen',
                'linea_investigacion_id' => 'línea de investigación',
                'metodologia_id' => 'metodología',
                'tipo_investigacion_id' => 'tipo de investigación',
                'objetivo_investigacion_id' => 'objetivo de investigación',
                'comunidad_id' => 'comunidad',
                'equipo_seccion_clave' => 'equipo y sección',
            ]);
        }

        $documentos = $esProfesor ? [] : $request->file('documentos', []);

        $this->gestion->guardar(
            (int) $id,
            $estadoForm,
            $user,
            $documentos,
            [],
        );

        if ($modoActualizacion) {
            $updateData = [
                'actualizado_por_estudiante' => true,
                'fecha_actualizacion_estudiante' => now(),
                'estado_logico' => true,
            ];

            // Si estaba rechazado, volver a pendiente para re-evaluación
            if ($proyecto->estado_validacion === 'rechazado') {
                $updateData['estado_validacion'] = 'pendiente';
                $updateData['motivo_rechazo'] = null;
            }

            $proyecto->update($updateData);

            $proyecto = $proyecto->fresh();

            try {
                // Buscar el profesor real que creó el grupo (no el creador del proyecto)
                $cedulaProfesor = $proyecto->creador_cedula;
                $clave = $proyecto->equipo_ref;
                if ($clave) {
                    $grupo = \App\Models\GrupoProyectoModulo::porIdentificador($clave);
                    if (!$grupo && str_starts_with($clave, 'EQGRP:')) {
                        $codigo = (int) substr($clave, 6);
                        $grupo = \App\Models\GrupoProyectoModulo::find($codigo);
                    }
                    if ($grupo && $grupo->grp_creador_cedula) {
                        $cedulaProfesor = trim((string) $grupo->grp_creador_cedula);
                    }
                }
                $this->notificacionService->notificarActualizacionEstudiante($proyecto, $cedulaProfesor);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Error notificando actualización: " . $e->getMessage());
            }
        }

        $proyecto = $proyecto->fresh(['documentos']);
        $completos = $this->gestion->verificarSiProyectoEstaCompletado($proyecto);
        if ($completos && $proyecto->estado_validacion !== 'aprobado') {
            $proyecto->update(['estado_validacion' => 'completado']);
        } elseif (!$completos && $proyecto->estado_validacion === 'completado') {
            $proyecto->update(['estado_validacion' => 'pendiente']);
        }

        return redirect()->route('proyectos.gestion')
            ->with('success', 'Proyecto actualizado con éxito.');
    }

    public function approve($id)
    {
        try {
            $proyecto = \App\Models\Proyecto::findOrFail($id);
            $estadoAnterior = $proyecto->estado_validacion;
            $this->gestion->aprobar((int) $id);
            $msg = $estadoAnterior === 'pendiente'
                ? 'Proyecto marcado como completado. El administrador debe aprobarlo.'
                : 'Proyecto aprobado con éxito.';

            if ($estadoAnterior === 'completado') {
                try {
                    $proyecto->refresh();
                    $grupo = \App\Models\GrupoProyectoModulo::porIdentificador($proyecto->equipo_ref ?? '');
                    $cedulas = collect($grupo?->grp_miembros ?? [])->pluck('cedula')->filter()->values()->toArray();
                    $this->notificacionService->notificarProyectoAprobado($proyecto, $cedulas);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Error notificando aprobación: " . $e->getMessage());
                }
            }

            return redirect()->route('proyectos.gestion')
                ->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->route('proyectos.gestion')
                ->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|min:10',
        ], [
            'motivo.required' => 'Debe indicar el motivo de rechazo.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
        ], [
            'motivo' => 'motivo de rechazo',
        ]);

        try {
            $this->gestion->rechazar((int) $id, $request->input('motivo'));

            // Notificar a estudiantes del proyecto rechazado
            try {
                $proyecto = \App\Models\Proyecto::findOrFail($id);
                $grupo = \App\Models\GrupoProyectoModulo::porIdentificador($proyecto->equipo_ref ?? '');
                $cedulas = collect($grupo?->grp_miembros ?? [])->pluck('cedula')->filter()->values()->toArray();
                if (!empty($cedulas)) {
                    $this->notificacionService->notificarProyectoRechazado(
                        $proyecto,
                        $request->input('motivo'),
                        $cedulas
                    );
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Error notificando rechazo de proyecto: " . $e->getMessage());
            }

            return redirect()->route('proyectos.gestion')
                ->with('success', 'Proyecto rechazado.');
        } catch (\Throwable $e) {
            return redirect()->route('proyectos.gestion')
                ->with('error', $e->getMessage());
        }
    }

    public function registrarDesdeGrupo(Request $request, $grpCodigo)
    {
        $user = auth()->user();
        $activeRole = $this->userRoleService->getActiveRole($user);

        $grupo = \App\Models\GrupoProyectoModulo::porIdentificador($grpCodigo);
        if (!$grupo) {
            $grupo = app(\App\Services\GrupoProyectoService::class)->obtener((int) $grpCodigo);
        }
        if (!$grupo) {
            return redirect()->route('proyectos.gestion')
                ->with('error', 'Grupo no encontrado.');
        }

        // Solo el profesor proyecto creador o miembros del equipo pueden crear proyecto desde el grupo
        $esProfesorCreador = false;
        $esMiembro = false;
        $usuarioCedula = trim((string) $user->usu_cedula);
        $usuarioUsuNombre = trim((string) $user->usu_nombre);
        if ($activeRole === 'profesor proyecto') {
            $ctx = $grupo->grp_contexto;
            $creadorUsu = trim((string) ($ctx['creador_usuario'] ?? ''));
            $creadorCed = trim((string) ($ctx['creador_cedula'] ?? $grupo->grp_creador_cedula ?? ''));
            $matchUsu = $creadorUsu !== '' && $creadorUsu === $usuarioUsuNombre;
            $matchCed = !$matchUsu && $creadorCed !== '' && $creadorCed === $usuarioCedula;
            $esProfesorCreador = $matchUsu || $matchCed;
        }
        foreach (($grupo->grp_miembros ?? []) as $m) {
            if (trim((string) ($m['cedula'] ?? '')) === $usuarioCedula) {
                $esMiembro = true;
                break;
            }
        }
        if (!$esMiembro && !$esProfesorCreador) {
            return redirect()->route('proyectos.gestion')
                ->with('error', 'No tienes permiso para crear un proyecto desde este grupo.');
        }

        $proyecto = $this->gestion->registrarProyectoDesdeGrupo((int) $grupo->grp_codigo, $user);

        if (!$proyecto) {
            return redirect()->route('proyectos.gestion')
                ->with('error', 'No se pudo registrar el proyecto desde el grupo.');
        }

        // Notificar a estudiantes del equipo
        try {
            $grupo = \App\Models\GrupoProyectoModulo::porIdentificador($proyecto->equipo_ref ?? '');
            if ($grupo) {
                $cedulas = collect($grupo->grp_miembros ?? [])->pluck('cedula')->filter()->values()->toArray();
                if (!empty($cedulas)) {
                    $this->notificacionService->notificarNuevoProyectoDesdeGrupo(
                        $proyecto,
                        $grupo->grp_nombre,
                        $cedulas
                    );
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Error notificando nuevo proyecto desde grupo: " . $e->getMessage());
        }

        return redirect()->route('proyectos.gestion.edit', $proyecto->id)
            ->with('success', 'Proyecto registrado desde el grupo. Complete los datos.');
    }

    // ─── Involucrados AJAX ───────────────────────────────────────────

    public function buscarInvolucrados(Request $request, $id)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }
        return response()->json($this->gestion->buscarInvolucrados($q)->values());
    }

    public function buscarPersonaPorCedula(Request $request)
    {
        $cedula = $request->get('cedula', '');
        if (strlen($cedula) < 3) {
            return response()->json(null);
        }
        return response()->json($this->gestion->buscarPersonaPorCedula($cedula));
    }

    public function buscarRoles(Request $request, $id)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 1) {
            return response()->json([]);
        }
        return response()->json($this->gestion->buscarRoles($q)->values());
    }

    public function agregarInvolucrado(Request $request, $id)
    {
        if ($this->esSupervisorLectura()) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'No tienes permiso.'], 403)
                : redirect()->route('proyectos.gestion')->with('error', 'No tienes permiso.');
        }

        $request->validate([
            'involucrado_id' => 'required|integer',
            'roles' => 'required|array|min:1',
            'roles.*' => 'integer',
        ], [
            'roles.required' => 'Debe asignar al menos un rol al involucrado.',
            'roles.min' => 'Debe asignar al menos un rol al involucrado.',
        ], [
            'involucrado_id' => 'involucrado',
            'roles' => 'roles',
            'roles.*' => 'rol',
        ]);

        $this->gestion->agregarInvolucradoAProyecto(
            (int) $id,
            (int) $request->input('involucrado_id'),
            $request->input('roles', [])
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Involucrado agregado al proyecto.');
    }

    public function crearInvolucrado(Request $request, $id)
    {
        if ($this->esSupervisorLectura()) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'No tienes permiso.'], 403)
                : redirect()->route('proyectos.gestion')->with('error', 'No tienes permiso.');
        }

        $request->validate([
            'nombre' => 'required|min:2|max:255',
            'apellido' => 'required|min:2|max:255',
            'cedula' => 'required|min:5|max:20',
            'roles' => 'required|array|min:1',
            'roles.*' => 'integer',
        ], [
            'roles.required' => 'Debe asignar al menos un rol al involucrado.',
            'roles.min' => 'Debe asignar al menos un rol al involucrado.',
        ], [
            'nombre' => 'nombre',
            'apellido' => 'apellido',
            'cedula' => 'cédula',
            'roles' => 'roles',
            'roles.*' => 'rol',
        ]);

        $involucrado = $this->gestion->crearInvolucrado(
            $request->input('nombre'),
            $request->input('apellido'),
            $request->input('cedula')
        );

        $pivotId = $this->gestion->agregarInvolucradoAProyecto(
            (int) $id,
            $involucrado->id,
            $request->input('roles', [])
        );

        if ($request->wantsJson()) {
            $connection = (string) config('dual_database.repositorio_connection', 'pgsql');
            $roleNames = DB::connection($connection)
                ->table('roles_involucrados')
                ->whereIn('id', $request->input('roles', []))
                ->pluck('nombre', 'id');
            $roles = [];
            foreach ($request->input('roles', []) as $rolId) {
                $roles[] = ['id' => (int) $rolId, 'nombre' => $roleNames->get($rolId, '')];
            }
            return response()->json([
                'success' => true,
                'id' => $involucrado->id,
                'nombre' => $involucrado->nombre,
                'apellido' => $involucrado->apellido,
                'cedula' => $involucrado->cedula,
                'pivot_id' => $pivotId,
                'roles' => $roles,
            ]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Involucrado creado y agregado al proyecto.');
    }

    public function quitarInvolucrado(Request $request, $id, $invId)
    {
        if ($this->esSupervisorLectura()) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'No tienes permiso.'], 403)
                : redirect()->route('proyectos.gestion')->with('error', 'No tienes permiso.');
        }

        $this->gestion->quitarInvolucradoDeProyecto((int) $id, (int) $invId);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Involucrado eliminado del proyecto.');
    }

    public function agregarRolInvolucrado(Request $request, $id, $invId)
    {
        if ($this->esSupervisorLectura()) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'No tienes permiso.'], 403)
                : redirect()->route('proyectos.gestion')->with('error', 'No tienes permiso.');
        }

        $request->validate([
            'rol_id' => 'required|integer',
        ], [
            'rol_id.required' => 'Debe seleccionar un rol.',
            'rol_id.integer' => 'El rol seleccionado no es válido.',
        ], [
            'rol_id' => 'rol',
        ]);

        $pivotId = $this->gestion->agregarInvolucradoAProyecto(
            (int) $id,
            (int) $invId,
            [(int) $request->input('rol_id')]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'pivot_id' => $pivotId]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Rol asignado al involucrado.');
    }

    public function quitarRolInvolucrado(Request $request, $id, $pivotId, $rolId)
    {
        if ($this->esSupervisorLectura()) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'No tienes permiso.'], 403)
                : redirect()->route('proyectos.gestion')->with('error', 'No tienes permiso.');
        }

        $this->gestion->quitarRolDeInvolucrado((int) $pivotId, (int) $rolId);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Rol eliminado del involucrado.');
    }

    public function crearRol(Request $request)
    {
        if ($this->esSupervisorLectura()) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'No tienes permiso.'], 403)
                : back()->with('error', 'No tienes permiso.');
        }

        $request->validate([
            'nombre' => 'required|min:2|max:255',
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
        ], [
            'nombre' => 'nombre del rol',
        ]);

        $rol = $this->gestion->crearRol($request->input('nombre'));

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $rol->id, 'nombre' => $rol->nombre]);
        }
        return back()->with('success', 'Rol creado correctamente.');
    }

    // ─── Reporte Excel (Depósito de Proyectos) ──────────────────────────────

    /**
     * Genera y descarga el reporte Excel del Depósito de Proyectos.
     * Accesible únicamente para administrador y coordinador.
     */
    public function exportarExcel(Request $request)
    {
        $user = auth()->user();
        $activeRole = $this->userRoleService->getActiveRole($user);

        $lapsoCodigo = $request->get('lapso', '');
        $lapsoNombre = '';
        if ($lapsoCodigo !== '') {
            try {
                $lap = \App\Models\LapsoAcademico::find((int) $lapsoCodigo);
                if ($lap) {
                    $lapsoNombre = trim($lap->lap_nombre ?? '');
                }
            } catch (\Throwable) {}
        }

        $filtros = [
            'search'            => $request->get('search', ''),
            'comunidad'         => $request->get('comunidad', ''),
            'lapso_codigo'      => $lapsoCodigo,
            'programa'          => $request->get('programa', ''),
            'trayecto'          => $request->get('trayecto', ''),
            'seccion'           => $request->get('seccion', ''),
            'linea'             => $request->get('linea', ''),
            'tipo_investigacion' => $request->get('tipo_investigacion', ''),
            'metodologia'       => $request->get('metodologia', ''),
        ];

        $datos   = $this->reporteDeposito->construirFilasReporte($filtros);
        $filas   = $datos['filas'];
        $maxInt  = $datos['maxIntegrantes'];
        $lapso   = $lapsoNombre ?: ($datos['lapsoMasActual'] ?? '');
        $pnf     = $datos['pnfPredominante'] ?? '';

        // ── Post-procesar sede fallback ────────────────────────────────────
        $proyectosEquipo = Proyecto::where('estado_validacion', 'aprobado')
            ->where('estado_logico', true)
            ->orderBy('id')
            ->select(['pry_codigo', 'pry_direccion_logica'])
            ->get()
            ->keyBy('pry_codigo');

        // Pre-cargar sedes necesarias (evita N+1)
        $sedesMap = [];
        $sedeRefs = [];
        foreach ($filas as $fila) {
            if (($fila['sede'] === '' || $fila['sede'] === '—') && isset($fila['pry_codigo'])) {
                $proy = $proyectosEquipo[$fila['pry_codigo']] ?? null;
                $equipoRef = $proy ? ($proy->equipo_ref ?? '') : '';
                if ($equipoRef !== '' && preg_match('/^[A-Z]+-([A-Z]{2,4})\d+-\d+/', strtoupper($equipoRef), $m)) {
                    $sedeRefs[$m[1]] = true;
                }
            }
        }
        if (!empty($sedeRefs)) {
            try {
                $academicConn = $this->equipoSeccion->academicConnection();
                $sedeConn = $academicConn === 'intranet' ? 'simulacion' : $academicConn;
                $sedesRows = DB::connection($sedeConn)->table('sede')
                    ->whereIn('sed_siglas', array_keys($sedeRefs))
                    ->get(['sed_siglas', 'sed_nombre']);
                foreach ($sedesRows as $s) {
                    $sedesMap[$s->sed_siglas] = strtoupper(trim($s->sed_nombre));
                }
            } catch (\Throwable) {}
        }

        foreach ($filas as &$fila) {
            $pryCodigo = isset($fila['pry_codigo']) ? (int) $fila['pry_codigo'] : 0;
            $proy = $pryCodigo > 0 ? ($proyectosEquipo[$pryCodigo] ?? null) : null;
            $equipoRef = $proy ? ($proy->equipo_ref ?? '') : '';

            // Sede fallback desde equipo_ref
            if (($fila['sede'] === '' || $fila['sede'] === '—') && $equipoRef !== '') {
                if (preg_match('/^[A-Z]+-([A-Z]{2,4})\d+-\d+/', strtoupper($equipoRef), $m)) {
                    $fila['sede'] = $sedesMap[$m[1]] ?? $m[1];
                }
            }

        }
        unset($fila);

        $writer  = new SpreadsheetMlWriter();
        $writer->setTitle('Proyectos Sociotecnologicos');

        // ── Columnas: 11 fijas + (Integrantes x 2) ────────────────────────
        $colsFijas       = 11;
        $colsIntegrantes = $maxInt * 2;
        $totalCols       = $colsFijas + $colsIntegrantes;

        // ── Fila de título ─────────────────────────────────────────────────
        $tituloReporte = 'UPTP JUAN DE JESÚS MONTILLA — PROYECTOS SOCIOTECNOLÓGICOS';
        if ($lapso !== '') {
            $tituloReporte .= ' — ' . mb_strtoupper($lapso);
        }
        $writer->addMergedTitleRow($tituloReporte, $totalCols);

        // ── Anchos de columna ─────────────────────────────────────────────
        $widths = [
            150,  // SEDE
            200,  // PROGRAMA NACIONAL DE FORMACIÓN
            100,  // TRAYECTO
            100,  // SEMESTRE
            450,  // TÍTULO DE PROYECTO
            300,  // RESUMEN O PRESENTACIÓN
            200,  // LÍNEA DE INVESTIGACIÓN
            280,  // DOCENTE DE PROYECTO
            280,  // TUTOR ACADEMICO
            280,  // REPRESENTANTE INSTITUCIONAL
        ];
        for ($i = 1; $i <= $maxInt; $i++) {
            $widths[] = 280; // INTEGRANTE Nº X – Nombre Completo
            $widths[] = 120; // INTEGRANTE Nº X – Cédula
        }
        $widths[] = 300; // LOCALIDAD GEOGRAFICA
        $widths[] = 120; // COMUNIDAD BENEFICIADA
        $widths[] = 130; // RESULTADO DE LA SOCIALIZACIÓN

        // ── Encabezados exactos del Excel ─────────────────────────────────
        $headers = [
            'SEDE',
            'PROGRAMA NACIONAL DE FORMACIÓN',
            'TRAYECTO',
            'SEMESTRE',
            'TÍTULO DE PROYECTO',
            'RESUMEN O PRESENTACIÓN (NO MAS DE 150 PALABRAS)',
            'LÍNEA DE INVESTIGACIÓN',
            'DOCENTE DE PROYECTO',
            'TUTOR ACADEMICO',
            'REPRESENTANTE INSTITUCIONAL',
        ];
        for ($i = 1; $i <= $maxInt; $i++) {
            $headers[] = "INTEGRANTE Nº {$i} – NOMBRE COMPLETO";
            $headers[] = "INTEGRANTE Nº {$i} – CÉDULA DE IDENTIDAD";
        }
        $headers[] = 'LOCALIDAD GEOGRAFICA DONDE SE DESARROLLÓ EL PROYECTO';
        $headers[] = 'COMUNIDAD BENEFICIADA';
        $headers[] = 'RESULTADO DE LA SOCIALIZACIÓN';

        $writer->addRow($headers, isHeader: true, height: 35, widths: $widths);

        // ── Filas de datos ────────────────────────────────────────────────
        foreach ($filas as $fila) {
            $celdas = [
                $fila['sede'],
                $fila['pnf'],
                $fila['trayecto'],
                $fila['semestre'],
                $fila['titulo'],
                $fila['resumen'],
                $fila['linea_investigacion'],
                $fila['docente'] ?? '',
                $fila['tutor_academico'],
                $fila['representante_institucional'],
            ];

            $integrantes = $fila['integrantes'];
            for ($i = 0; $i < $maxInt; $i++) {
                $celdas[] = $integrantes[$i]['nombre'] ?? '';
                $celdas[] = $integrantes[$i]['cedula'] ?? '';
            }

            $celdas[] = $fila['localidad_geografica'];
            $celdas[] = $fila['comunidad_beneficiada'];
            $celdas[] = $fila['resultado_socializacion'];

            $writer->addRow($celdas, wrap: true, height: 45);
        }

        // ── Nombre de archivo ─────────────────────────────────────────────
        $partesPnf   = $pnf   !== '' ? ' ' . mb_strtoupper($pnf)   : '';
        $partesLapso = $lapso !== '' ? ' ' . mb_strtoupper($lapso) : (' ' . now()->format('Y'));
        $filename = 'PROYECTOS SOCIOTECNOLÓGICOS' . $partesPnf . $partesLapso . '.xls';

        return $writer->download($filename);
    }

    public function exportProgramas($lapso)
    {
        try {
            return response()->json($this->equipoSeccion->programasEnLapso((int) $lapso)->values());
        } catch (\Throwable $e) {
            return response()->json([]);
        }
    }

    public function exportTrayectos($lapso)
    {
        try {
            $programa = request()->query('programa') ? (int) request()->query('programa') : null;
            return response()->json($this->equipoSeccion->trayectosEnLapso((int) $lapso, $programa)->values());
        } catch (\Throwable $e) {
            return response()->json([]);
        }
    }

    public function exportSecciones($lapso)
    {
        try {
            $programa = request()->query('programa') ? (int) request()->query('programa') : null;
            $trayecto = request()->query('trayecto') ? (int) request()->query('trayecto') : null;
            return response()->json($this->equipoSeccion->seccionesEnLapso((int) $lapso, $programa, $trayecto)->values());
        } catch (\Throwable $e) {
            return response()->json([]);
        }
    }

    public function buscarProyectosAjax(Request $request)
    {
        $search = $request->query('q', '');
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        // Filtros adicionales del modal de exportación
        $filters = [
            'comunidad'         => $request->query('comunidad', ''),
            'lapso'             => $request->query('lapso', ''),
            'linea'             => $request->query('linea', ''),
            'tipo_investigacion' => $request->query('tipo_investigacion', ''),
            'metodologia'       => $request->query('metodologia', ''),
        ];

        // Cache key incluye filtros
        $cacheKey = 'proyectos_ajax_search_' . md5(json_encode([
            'q' => strtolower(trim($search)),
            'f' => $filters,
        ]));

        $results = \Illuminate\Support\Facades\Cache::remember($cacheKey, 15, function () use ($search, $filters) {
            $searchTrimmed = trim($search);
            $termino = '%' . $searchTrimmed . '%';

            $query = Proyecto::where('estado_validacion', 'aprobado')
                ->where('estado_logico', true)
                ->where(function ($q) use ($searchTrimmed, $termino) {
                    // Resumen del proyecto
                    try {
                        $q->whereRaw(
                            'to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')) @@ plainto_tsquery(\'spanish\', ?)',
                            [$searchTrimmed]
                        );
                    } catch (\Throwable) {
                        $q->whereRaw('pry_resumen ILIKE ?', [$termino]);
                    }
                    // Nombre del grupo
                    $q->orWhereRaw('pry_direccion_logica ILIKE ?', [$termino]);
                    $q->orWhereRaw('EXISTS (SELECT 1 FROM grupo_proyecto_modulo g WHERE g.grp_identificador = proyectos.pry_direccion_logica AND g.grp_nombre ILIKE ?)', [$termino]);
                    $q->orWhereRaw('EXISTS (SELECT 1 FROM grupo_proyecto_modulo g WHERE g.grp_codigo::text = regexp_replace(proyectos.pry_direccion_logica, E\'^EQGRP:\', \'\') AND g.grp_nombre ILIKE ?)', [$termino]);
                    // Comunidad
                    $q->orWhereHas('comunidad', function ($qc) use ($termino) {
                        $qc->whereRaw('com_nombre ILIKE ?', [$termino]);
                    });
                    // Línea de investigación
                    $q->orWhereHas('linea_investigacion', function ($ql) use ($termino) {
                        $ql->whereRaw('lin_nombre_investigacion ILIKE ?', [$termino]);
                    });
                    // Metodología
                    $q->orWhereHas('metodologia', function ($qm) use ($termino) {
                        $qm->whereRaw('mei_nombre ILIKE ?', [$termino]);
                    });
                    // Tipo de investigación
                    $q->orWhereHas('tipo_investigacion', function ($qt) use ($termino) {
                        $qt->whereRaw('tin_nombre ILIKE ?', [$termino]);
                    });
                    // Código exacto
                    if (is_numeric($searchTrimmed)) {
                        $q->orWhere('pry_codigo', (int) $searchTrimmed);
                    }
                });

            // Aplicar filtros adicionales
            if (($filters['comunidad'] ?? '') !== '') {
                $query->where('comunidad_id', (int) $filters['comunidad']);
            }
            if (($filters['linea'] ?? '') !== '') {
                $query->where('linea_investigacion_id', (int) $filters['linea']);
            }
            if (($filters['tipo_investigacion'] ?? '') !== '') {
                $query->where('tipo_investigacion_id', (int) $filters['tipo_investigacion']);
            }
            if (($filters['metodologia'] ?? '') !== '') {
                $query->where('metodologia_id', (int) $filters['metodologia']);
            }

            // Filtro por lapso: buscar en equipo_ref que contenga el lapso
            if (($filters['lapso'] ?? '') !== '') {
                $lapCodigo = (int) $filters['lapso'];
                $query->where(function ($q) use ($lapCodigo) {
                    $q->where('pry_direccion_logica', 'LIKE', "EQSEC:{$lapCodigo}:%")
                      ->orWhereIn('pry_direccion_logica', function ($sub) use ($lapCodigo) {
                          $sub->select('grp_identificador')
                              ->from('grupo_proyecto_modulo')
                              ->whereRaw("CAST(grp_contexto AS jsonb)->>'lap_codigo' = ?", [(string) $lapCodigo])
                              ->where('estado_logico', true)
                              ->whereNotNull('grp_identificador');
                      });
                });
            }

            $proyectos = $query->orderByDesc('pry_codigo')
                ->limit(15)
                ->get(['pry_codigo', 'pry_resumen', 'pry_direccion_logica']);

            $results = [];
            foreach ($proyectos as $p) {
                $results[] = [
                    'id'      => (int) $p->pry_codigo,
                    'title'   => $p->titulo,
                    'resumen' => mb_substr(strip_tags($p->resumen ?? ''), 0, 100),
                ];
            }

            return $results;
        });

        return response()->json($results);
    }

    public function actualizarEstadoDocumento(Request $request, $id)
    {
        $user = auth()->user();
        $activeRole = $this->userRoleService->getActiveRole($user);
        if (!$this->userRoleService->roleMatches('profesor proyecto', $activeRole) &&
            !$this->userRoleService->roleMatches('gestionador', $activeRole)) {
            return response()->json(['success' => false, 'message' => 'No tiene permisos.'], 403);
        }

        $request->validate([
            'estado' => 'required|in:1,2',
            'observacion' => 'required_if:estado,2|nullable|string|max:500',
        ], [
            'estado.required' => 'Debe seleccionar un estado para el documento.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'observacion.required_if' => 'Debe indicar una observación cuando rechaza el documento.',
            'observacion.max' => 'La observación no puede exceder 500 caracteres.',
        ], [
            'estado' => 'estado del documento',
            'observacion' => 'observación',
        ]);

        try {
            $doc = \App\Models\ProyectoDocumento::findOrFail($id);
            $nuevoEstado = $request->input('estado');
            $observacion = $request->input('observacion', '');

            $doc->update([
                'pd_estado' => $nuevoEstado,
                'pd_observacion' => $observacion,
            ]);

            // Cargar proyecto directamente desde el pry_codigo del documento
            $proyectoId = $doc->getAttribute('pry_codigo');
            $proyecto = $proyectoId ? \App\Models\Proyecto::find($proyectoId) : null;

            if ($proyecto) {
                $grupo = \App\Models\GrupoProyectoModulo::porIdentificador($proyecto->equipo_ref ?? '');
                $cedulas = collect($grupo?->grp_miembros ?? [])->pluck('cedula')->filter()->values()->toArray();

                if ($nuevoEstado == 1) {
                    $allDocs = \App\Models\ProyectoDocumento::where('pry_codigo', $proyecto->getKey())->get();
                    $todosAceptados = $allDocs->isNotEmpty() && $allDocs->every(fn ($d) => (int) $d->pd_estado === 1);
                    $completos = $todosAceptados && $this->gestion->verificarSiProyectoEstaCompletado($proyecto);

                    if ($completos && $proyecto->estado_validacion !== 'aprobado') {
                        $proyecto->update(['estado_validacion' => 'completado']);
                        \Illuminate\Support\Facades\Log::info("Proyecto {$proyecto->getKey()} auto-completado: todos los documentos aceptados y componentes completos.");

                        if (!empty($cedulas)) {
                            $this->notificacionService->notificarProyectoCompletado($proyecto, $cedulas);
                        }
                    }

                    $this->notificacionService->notificarDocumentoAceptado(
                        $proyecto,
                        $doc->componente->nombre ?? 'Documento',
                        $cedulas
                    );
                } elseif ($nuevoEstado == 2) {
                    $this->notificacionService->notificarDocumentoRechazado(
                        $proyecto,
                        $doc->componente->nombre ?? 'Documento',
                        $observacion,
                        $cedulas
                    );
                }
            } else {
                \Illuminate\Support\Facades\Log::warning("actualizarEstadoDocumento: no se encontró proyecto para documento {$id}, pry_codigo=" . var_export($proyectoId, true));
            }

            return response()->json(['success' => true, 'message' => 'Estado del documento actualizado correctamente.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function solvencia($id, $cedula = null)
    {
        try {
            // Si no se proporcionó cédula, usar la del usuario autenticado
            if ($cedula === null) {
                $user = auth()->user();
                $cedula = $user ? trim((string) $user->usu_cedula) : '';
            }

            $datos  = $this->gestion->datosSolvencia((int) $id, $cedula);
            $now    = now();

            // Tomar el primer (y único) integrante filtrado
            $integrante = $datos['integrantes'][0] ?? null;

            $pnfUpper = mb_strtoupper($datos['pnf_nombre'] ?? '');
            $isInf = str_contains($pnfUpper, 'INF');
            $tipoProyecto = $isInf ? 'Proyecto Sociotecnológico' : 'Proyecto Sociocomunitario';
            $pnfLimpio = str_ireplace('PROGRAMA NACIONAL DE FORMACIÓN EN ', '', $pnfUpper);

            $userCreator = \App\Models\User::where('usu_cedula', trim((string) $datos['creador_cedula']))->first();
            $nombreProfesor = $userCreator ? ($userCreator->nombre . ' ' . $userCreator->apellido) : 'No disponible';

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.solvencia', [
                'integrante'     => $integrante,
                'titulo_proyecto'=> $datos['titulo_proyecto'],
                'comunidad'      => $datos['comunidad'],
                'pnf'            => $datos['pnf'],
                'pnf_nombre'     => $datos['pnf_nombre'],
                'pnf_limpio'     => $pnfLimpio,
                'tipoProyecto'   => $tipoProyecto,
                'trayecto'       => $datos['trayecto'],
                'seccion'        => $datos['seccion'],
                'lapso'          => $datos['lapso'],
                'profesor_responsable' => $nombreProfesor,
                'dia'            => $now->day,
                'mes'            => ucfirst($now->translatedFormat('F')),
                'anio'           => $now->year,
            ]);

            $nombreArchivo = 'Solvencia_' . ($integrante ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $integrante['nombre_completo']) : 'integrante');

            return $pdf->download("{$nombreArchivo}.pdf");
        } catch (\RuntimeException $e) {
            return redirect()->route('proyectos.gestion')
                ->with('error', $e->getMessage());
        }
    }

    protected function buildEstadoFromDatos(array $datos): array
    {
        return [
            'equipo_seccion_clave' => $datos['equipo_seccion_clave'] ?? '',
            'filterLapsoEquipo' => $datos['filterLapsoEquipo'] ?? '',
            'filterProgramaEquipo' => $datos['filterProgramaEquipo'] ?? '',
            'filterSeccionEquipo' => $datos['filterSeccionEquipo'] ?? '',
            'programa_id' => $datos['programa_id_derived'] ?? null,
            'trayecto' => $datos['trayecto_derived'] ?? '',
            'trayecto_codigo' => $datos['trayecto_derived_codigo'] ?? '',
            'titulo' => $datos['titulo'] ?? '',
            'resumen' => $datos['resumen'] ?? '',
            'linea_investigacion_id' => $datos['linea_investigacion_id'] ?? '',
            'metodologia_id' => $datos['metodologia_id'] ?? '',
            'tipo_investigacion_id' => $datos['tipo_investigacion_id'] ?? '',
            'objetivo_investigacion_id' => $datos['objetivo_investigacion_id'] ?? '',
            'comunidad_id' => $datos['comunidad_id'] ?? '',
            'cantidad_beneficiados' => $datos['pry_cantidad_beneficiados'] ?? 0,
        ];
    }

    protected function cargarMiembrosGrupo(string $clave, array &$miembros): void
    {
        try {
            $integrantes = $this->grupos->integrantes($clave);
            $miembros = $integrantes->map(fn($m) => [
                'cedula' => $m->cedula,
                'nombre' => $m->nombre,
                'apellido' => $m->apellido ?? '',
                'rol_id' => $m->rol_id ?? 0,
            ])->toArray();
        } catch (\Throwable) {
            $miembros = [];
        }
    }

    protected function esSupervisorLectura(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        $activeRole = $this->userRoleService->getActiveRole($user);
        return $this->userRoleService->roleMatches('administrador', $activeRole)
            || $this->userRoleService->roleMatches('coordinador', $activeRole);
    }
}
