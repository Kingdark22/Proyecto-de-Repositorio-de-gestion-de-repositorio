<?php

namespace App\Http\Controllers;

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

        $search = $request->get('search', '');
        $filterEstado = $request->get('estado', '');
        $filterComunidad = $request->get('comunidad', '');
        $filterLapso = $request->get('lapso', '');
        $page = (int) $request->get('page', 1);

        // Grupos del docente
        $gruposDocente = [];
        if (in_array($activeRole, ['profesor proyecto', 'administrador', 'gestionador', 'coordinador'])) {
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

        $canValidate = $user ? $this->gestion->usuarioPuedeValidar($user) : false;
        $proyectosLiderIds = $this->gestion->proyectosDondeEsMiembro($user);

        $proyectosConDocumentosRechazados = \App\Models\ProyectoDocumento::where('pd_estado', 2)
            ->distinct()
            ->pluck('pry_codigo')
            ->toArray();

        return view('proyectos.index', compact(
            'search', 'filterEstado', 'filterComunidad', 'filterLapso',
            'esProfesor', 'esAdmin', 'esGestionador', 'esEstudianteLider',
            'gruposDocente', 'proyectosLider', 'proyectosLiderIds',
            'datosListado', 'mostrarListado', 'canValidate',
            'lapsosFiltro', 'programasFiltro', 'trayectosFiltro',
            'proyectosConDocumentosRechazados',
        ));
    }

    public function edit($id)
    {
        $user = auth()->user();
        $activeRole = $this->userRoleService->getActiveRole($user);
        $esProfesor = $this->userRoleService->roleMatches('profesor proyecto', $activeRole);
        $esGestionador = $this->userRoleService->roleMatches('gestionador', $activeRole);
        $esAdmin = $this->userRoleService->roleMatches('administrador', $activeRole);

        $proyecto = Proyecto::findOrFail($id);
        $esMiembro = $this->gestion->usuarioEsMiembroDelProyecto($user, $proyecto);
        $esCreador = $user && trim((string) $user->usu_cedula) === trim((string) $proyecto->creador_cedula);
        $esAdminEnSistema = $this->gestion->usuarioEsAdminEnSistema($user);
        $esAdminProyectoAjeno = $esAdmin && !$esCreador;
        $modoActualizacion = $esMiembro && !$esAdminEnSistema && !$esAdminProyectoAjeno;
        $canValidate = $user ? $this->gestion->usuarioPuedeValidar($user) : false;

        // Admin y Coordinador solo pueden ver proyectos en modo solo lectura
        $adminPuedeEditar = false;
        $esCoordinador = $this->userRoleService->roleMatches('coordinador', $activeRole);

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
            'canValidate', 'esAdminProyectoAjeno', 'adminPuedeEditar', 'esAdmin', 'esCoordinador',
            'esCreador', 'esMiembro',
        ));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $proyecto = Proyecto::findOrFail($id);

        $activeRole = $this->userRoleService->getActiveRole($user);
        $esProfesor = $this->userRoleService->roleMatches('profesor proyecto', $activeRole);
        $esAdmin = $this->userRoleService->roleMatches('administrador', $activeRole);

        // Admin no puede actualizar proyectos
        if ($esAdmin) {
            return redirect()->route('proyectos.gestion')
                ->with('error', 'El administrador no puede actualizar proyectos. Solo tiene permisos de lectura.');
        }

        $esMiembro = $this->gestion->usuarioEsMiembroDelProyecto($user, $proyecto);
        $esAdminEnSistema = $this->gestion->usuarioEsAdminEnSistema($user);

        $modoActualizacion = $esMiembro && !$esAdminEnSistema;

        $estadoForm = [
            'resumen' => $request->input('resumen', $proyecto->resumen ?? ''),
            'linea_investigacion_id' => $request->input('linea_investigacion_id', $proyecto->linea_investigacion_id),
            'metodologia_id' => $request->input('metodologia_id', $proyecto->metodologia_id),
            'tipo_publicacion_id' => $request->input('tipo_publicacion_id', $proyecto->tipo_publicacion_id),
            'tipo_investigacion_id' => $request->input('tipo_investigacion_id', $proyecto->tipo_investigacion_id),
            'objetivo_investigacion_id' => $request->input('objetivo_investigacion_id', $proyecto->objetivo_investigacion_id),
            'titulo' => $proyecto->titulo,
            'comunidad_id' => $request->input('comunidad_id', $proyecto->comunidad_id),
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
                'tipo_publicacion_id' => 'tipo de publicación',
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
            if ($this->gestion->verificarSiProyectoEstaCompletado($proyecto)) {
                if ($proyecto->estado_validacion === 'pendiente') {
                    $proyecto->update(['estado_validacion' => 'completado']);
                }
            } else {
                if ($proyecto->estado_validacion === 'completado') {
                    $proyecto->update(['estado_validacion' => 'pendiente']);
                }
            }

            try {
                // Buscar el profesor real que creó el grupo (no el creador del proyecto)
                $cedulaProfesor = $proyecto->creador_cedula;
                $clave = $proyecto->equipo_ref;
                if ($clave) {
                    $grupo = \App\Models\GrupoProyectoModulo::where('grp_identificador', $clave)->first();
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

        return redirect()->route('proyectos.gestion')
            ->with('success', 'Proyecto actualizado con éxito.');
    }

    public function toggleStatus($id)
    {
        $this->gestion->alternarEstado((int) $id);
        return redirect()->route('proyectos.gestion')
            ->with('success', 'Estado del proyecto actualizado.');
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
                    $grupo = \App\Models\GrupoProyectoModulo::where('grp_identificador', $proyecto->equipo_ref)->first();
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
                $grupo = \App\Models\GrupoProyectoModulo::where('grp_identificador', $proyecto->equipo_ref)->first();
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

    public function destroy($id)
    {
        $user = auth()->user();
        $proyecto = Proyecto::findOrFail($id);
        $esAdmin = $this->userRoleService->roleMatches('administrador', $this->userRoleService->getActiveRole($user));

        if ($esAdmin && $user->usu_cedula != $proyecto->creador_cedula) {
            abort(403, 'No tienes permiso para eliminar este proyecto.');
        }

        $this->gestion->eliminar((int) $id);
        return redirect()->route('proyectos.gestion')
            ->with('success', 'Proyecto eliminado correctamente.');
    }

    public function registrarDesdeGrupo(Request $request, $grpCodigo)
    {
        $user = auth()->user();
        $proyecto = $this->gestion->registrarProyectoDesdeGrupo((int) $grpCodigo, $user);

        if (!$proyecto) {
            return redirect()->route('proyectos.gestion')
                ->with('error', 'No se pudo registrar el proyecto desde el grupo.');
        }

        // Notificar a estudiantes del equipo
        try {
            $grupo = \App\Models\GrupoProyectoModulo::where('grp_identificador', $proyecto->equipo_ref)->first();
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

        $this->gestion->agregarInvolucradoAProyecto(
            (int) $id,
            $involucrado->id,
            $request->input('roles', [])
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $involucrado->id]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Involucrado creado y agregado al proyecto.');
    }

    public function quitarInvolucrado(Request $request, $id, $invId)
    {
        $this->gestion->quitarInvolucradoDeProyecto((int) $id, (int) $invId);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Involucrado eliminado del proyecto.');
    }

    public function agregarRolInvolucrado(Request $request, $id, $invId)
    {
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
        $this->gestion->quitarRolDeInvolucrado((int) $pivotId, (int) $rolId);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Rol eliminado del involucrado.');
    }

    public function crearRol(Request $request)
    {
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

        // ── Filtro de lapso (opcional — si se envía, filtra por ese lapso) ──
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

        // ── Detectar PNF del usuario ───────────────────────────────────────
        $proSiglas = $request->get('pnf', '');
        if ($proSiglas === '') {
            // Intentar detectar PNF desde las secciones del usuario
            try {
                $proCodigos = app(\App\Services\IntranetProfessorService::class)
                    ->programasDelDocente(trim($user->usu_cedula));

                if (count($proCodigos) === 1) {
                    // Un solo PNF → usarlo automáticamente
                    $prog = \Illuminate\Support\Facades\DB::connection(
                        app(\App\Services\IntranetEquipoSeccionService::class)->academicConnection()
                    )->table('programa')->where('pro_codigo', $proCodigos[0])->first();
                    if ($prog) {
                        $proSiglas = trim($prog->pro_siglas ?? '');
                    }
                }
            } catch (\Throwable) {}

            // Si sigue vacío y el rol es admin/coordinador sin PNF definido, se exportan todos
        }

        $filtros = [
            'estado'       => $request->get('estado', ''),
            'comunidad'    => $request->get('comunidad', ''),
            'lapso_codigo' => $lapsoCodigo,
            'pro_siglas'   => $proSiglas,
        ];

        $datos   = $this->reporteDeposito->construirFilasReporte($filtros);
        $filas   = $datos['filas'];
        $maxInt  = $datos['maxIntegrantes'];
        $lapso   = $lapsoNombre ?: ($datos['lapsoMasActual'] ?? '');
        $pnf     = $datos['pnfPredominante'] ?? ($proSiglas ?: '');

        // ── Sede fallback: extraer desde equipo_ref cuando la sede vino vacía ──
        $proyectosEquipo = Proyecto::where('estado_validacion', 'aprobado')
            ->where('estado_logico', true)
            ->orderBy('id')
            ->select(['pry_codigo', 'pry_direccion_logica'])
            ->get();
        foreach ($filas as $idx => &$fila) {
            $proy = $proyectosEquipo[$idx] ?? null;
            $equipoRef = $proy ? ($proy->equipo_ref ?? '') : '';

            // Sede fallback desde equipo_ref
            if (($fila['sede'] === '' || $fila['sede'] === '—') && $equipoRef !== '') {
                if (preg_match('/^[A-Z]+-([A-Z]{2,4})\d+-\d+/', strtoupper($equipoRef), $m)) {
                    $sedSiglas = $m[1];
                    try {
                        $academicConn = $this->equipoSeccion->academicConnection();
                        $sedeConn = $academicConn === 'intranet' ? 'simulacion' : $academicConn;
                        $sedNombre = DB::connection($sedeConn)->table('sede')
                            ->where('sed_siglas', $sedSiglas)
                            ->value('sed_nombre') ?? $sedSiglas;
                        $fila['sede'] = strtoupper($sedNombre);
                    } catch (\Throwable) {
                        $fila['sede'] = $sedSiglas;
                    }
                }
            }

            // Docente de proyecto = quien creó el grupo de proyecto (grp_creador_cedula)
            $docente = '';
            try {
                $grupoCreador = \App\Models\GrupoProyectoModulo::where('grp_identificador', $equipoRef)->value('grp_creador_cedula');
                if ($grupoCreador) {
                    $creador = \App\Models\User::where('usu_cedula', trim((string) $grupoCreador))->first();
                    if ($creador) {
                        $docente = strtoupper(trim(($creador->nombre ?? '') . ' ' . ($creador->apellido ?? '')));
                    }
                }
            } catch (\Throwable) {}
            $fila['docente'] = $docente;
        }
        unset($fila);

        $writer  = new SpreadsheetMlWriter();
        $writer->setTitle('Proyectos Sociotecnologicos');

        // ── Calcular número total de columnas ──────────────────────────────
        $colsFijas       = 10;
        $colsIntegrantes = $maxInt * 2;
        $colsFinales     = 3;
        $totalCols       = $colsFijas + $colsIntegrantes + $colsFinales;

        // ── Fila de título ─────────────────────────────────────────────────
        $tituloReporte = 'UPTP JUAN DE JESÚS MONTILLA — PROYECTOS SOCIOTECNOLÓGICOS';
        if ($lapso !== '') {
            $tituloReporte .= ' — ' . mb_strtoupper($lapso);
        }
        $writer->addMergedTitleRow($tituloReporte, $totalCols);

        // ── Anchos de columna (puntos) ─────────────────────────────────────
        $widths = [
            35,   // N°
            150,  // Sede
            200,  // PNF
            100,  // Trayecto
            100,  // Sección
            130,  // Lapso
            400,  // Título
            250,  // Comunidad
            200,  // Equipo
            280,  // Docente
        ];
        for ($i = 0; $i < $maxInt; $i++) {
            $widths[] = 280; // Nombre
            $widths[] = 120; // Cédula
        }
        $widths[] = 280; // Tutor Académico
        $widths[] = 110; // Cumplió Requisitos
        $widths[] = 120; // Cant. Beneficiados

        // ── Encabezados ───────────────────────────────────────────────────
        $headers = [
            'N°', 'Sede', 'Programa Nacional de Formación', 'Trayecto', 'Sección',
            'Lapso Académico', 'Título del Proyecto', 'Comunidad', 'Nombre del Equipo',
            'Docente de Proyecto',
        ];
        for ($i = 1; $i <= $maxInt; $i++) {
            $headers[] = "Integrante {$i} – Nombre Completo";
            $headers[] = "Integrante {$i} – Cédula";
        }
        $headers[] = 'Tutor Académico';
        $headers[] = 'Cumplió Requisitos';
        $headers[] = 'Cantidad de Beneficiados';

        $writer->addRow($headers, isHeader: true, height: 35, widths: $widths);

        // ── Filas de datos ────────────────────────────────────────────────
        foreach ($filas as $fila) {
            $celdas = [
                $fila['numero'],
                $fila['sede'],
                $fila['pnf'],
                $fila['trayecto'],
                $fila['seccion'],
                $fila['lapso'],
                $fila['titulo'],
                $fila['comunidad'],
                $fila['equipo'],
                $fila['docente'] ?? '',
            ];

            $integrantes = $fila['integrantes'];
            for ($i = 0; $i < $maxInt; $i++) {
                $celdas[] = $integrantes[$i]['nombre'] ?? '';
                $celdas[] = $integrantes[$i]['cedula'] ?? '';
            }

            $celdas[] = $fila['tutor_academico'];
            $celdas[] = $fila['cumplio_requisitos'];
            $celdas[] = $fila['cant_beneficiados'];

            $writer->addRow($celdas, wrap: true, height: 45);
        }

        // ── Generar nombre de archivo ──────────────────────────────────────
        $partesPnf   = $pnf   !== '' ? ' ' . mb_strtoupper($pnf)   : '';
        $partesLapso = $lapso !== '' ? ' ' . mb_strtoupper($lapso) : (' ' . now()->format('Y'));
        $filename = 'DEPOSITO PROYECTOS' . $partesPnf . $partesLapso . '.xls';

        return $writer->download($filename);
    }

    public function actualizarEstadoDocumento(Request $request, $id)
    {
        $user = auth()->user();
        $activeRole = $this->userRoleService->getActiveRole($user);
        if (!$this->userRoleService->roleMatches('profesor proyecto', $activeRole) && 
            !$this->userRoleService->roleMatches('administrador', $activeRole) &&
            !$this->userRoleService->roleMatches('coordinador', $activeRole) &&
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

            try {
                $proyecto = $doc->proyecto;
                $grupo = \App\Models\GrupoProyectoModulo::where('grp_identificador', $proyecto->equipo_ref)->first();
                $cedulas = collect($grupo?->grp_miembros ?? [])->pluck('cedula')->filter()->values()->toArray();

                if ($nuevoEstado == 1) {
                    // Cuando se acepta un documento, verificar si TODOS están aceptados
                    $allDocs = \App\Models\ProyectoDocumento::where('pry_codigo', $proyecto->id)->get();
                    $todosAceptados = $allDocs->every(fn ($d) => (int) $d->pd_estado === 1);

                    if ($todosAceptados && $proyecto->estado_validacion !== 'aprobado') {
                        $proyecto->update(['estado_validacion' => 'completado']);
                        \Illuminate\Support\Facades\Log::info("Proyecto {$proyecto->id} auto-completado: todos los documentos aceptados.");

                        // Notificar a los estudiantes que el proyecto fue completado
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
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Error notificando estado de documento: " . $e->getMessage());
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
            'tipo_publicacion_id' => $datos['tipo_publicacion_id'] ?? '',
            'tipo_investigacion_id' => $datos['tipo_investigacion_id'] ?? '',
            'objetivo_investigacion_id' => $datos['objetivo_investigacion_id'] ?? '',
            'comunidad_id' => $datos['comunidad_id'] ?? '',
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
}
