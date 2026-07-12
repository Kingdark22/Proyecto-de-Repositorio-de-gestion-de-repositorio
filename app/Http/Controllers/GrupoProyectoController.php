<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use App\Models\Estado;
use App\Models\Municipio;
use App\Repositories\ProyectoRepository;
use App\Services\ComunidadGestionService;
use App\Services\GrupoProyectoService;
use App\Services\IntranetEquipoSeccionService;
use App\Services\IntranetProfessorService;
use App\Services\UnicidadNombreService;
use App\Services\UserRoleService;
use App\Services\ValidacionCorreoService;
use App\Services\ValidacionRifService;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GrupoProyectoController extends Controller
{
    public function __construct(
        protected GrupoProyectoService $grupos,
        protected IntranetEquipoSeccionService $equipos,
        protected IntranetProfessorService $profesores,
        protected ProyectoRepository $proyectoRepo,
        protected NotificacionService $notificacionService,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);
        $isProfessor = $activeRole === 'profesor proyecto';

        $tablaOk = $this->grupos->tablaDisponible();

        // Lapsos: coordinador ve todos, profesor solo activos
        $esCoordinador = $activeRole === 'coordinador';
        $lapsos = $esCoordinador
            ? $this->profesores->todosLosLapsos()
            : $this->profesores->lapsosActivos();

        // Filtros
        $filterLapso = $request->get('lapso', '');
        $filterPrograma = $request->get('programa', '');
        $filterSeccion = $request->get('seccion', '');
        $search = $request->get('search', '');

        // Si es profesor, forzar lapso vigente y filtrar por sus grupos
        if ($isProfessor && $filterLapso === '') {
            $lapsoVigente = $this->profesores->lapsoVigenteCodigo();
            if ($lapsoVigente) {
                $filterLapso = (string) $lapsoVigente;
            }
        }

        $lapCodigo = $filterLapso !== '' ? (int) $filterLapso : null;
        $programaCodigo = $filterPrograma !== '' ? (int) $filterPrograma : null;
        $seccionCodigo = $filterSeccion !== '' ? (int) $filterSeccion : null;

        // Programas disponibles
        if ($isProfessor && $lapCodigo) {
            $proCodigos = $this->profesores->programasDelDocente(
                trim((string) $user->usu_cedula),
                $lapCodigo,
            );
            $programas = $proCodigos !== []
                ? $this->equipos->programasEnLapso($lapCodigo)->whereIn('pro_codigo', $proCodigos)->values()
                : collect();
        } else {
            $programas = $this->equipos->programasEnLapso($lapCodigo);
        }

        // Secciones disponibles
        $secCodigos = [];
        if ($isProfessor && $lapCodigo) {
            $secCodigos = $this->profesores->seccionesDelDocente(
                trim((string) $user->usu_cedula),
                $lapCodigo,
            );
            $secciones = $secCodigos !== []
                ? $this->equipos->seccionesEnLapso($lapCodigo, $programaCodigo)->whereIn('sec_codigo', $secCodigos)->values()
                : collect();
        } else {
            $secciones = $this->equipos->seccionesEnLapso($lapCodigo, $programaCodigo);
        }

        // Construir filtros para listar grupos
        $filters = ['lapso' => $lapCodigo, 'programa' => $programaCodigo, 'busqueda' => $search];

        if ($isProfessor) {
            $filters['seccion'] = $secCodigos !== [] ? $secCodigos : [-1];
        } elseif ($activeRole === 'estudiante') {
            $filters['estudiante_cedula'] = trim((string) $user->usu_cedula);
        } elseif ($seccionCodigo) {
            $filters['seccion'] = $seccionCodigo;
        }

        $lista = collect();
        if ($tablaOk) {
            try {
                $lista = $this->grupos->listar($filters);
            } catch (\Throwable $e) {
                request()->session()->flash('error', 'Error: ' . $e->getMessage());
            }
        }

        // Si es profesor y no tiene grupos en el lapso actual, redirigir a crear grupo
        if ($isProfessor && $lista->isEmpty() && $lapCodigo) {
            return redirect()->route('grupos-proyecto.create')
                ->with('info', 'No tienes grupos registrados en este lapso. Crea tu primer grupo para comenzar.');
        }

        // Obtener proyectos asociados a los grupos
        $proyectoPorClave = collect();
        if ($lista->isNotEmpty()) {
            try {
                $claves = $lista->pluck('clave')->filter()->toArray();
                if ($claves !== []) {
                    $proyectoPorClave = $this->proyectoRepo->findByEquipos($claves)->keyBy('equipo_ref');
                }
            } catch (\Throwable $e) {
                Log::warning('Error cargando proyectos de grupos: ' . $e->getMessage());
            }
        }

        // Mapa de creador para grupos existentes sin creador_usuario en contexto
        $creadorNombres = collect();
        if ($lista->isNotEmpty()) {
            try {
                $cedulas = $lista->pluck('creador_cedula')->filter()->unique()->toArray();
                if ($cedulas !== []) {
                    \App\Models\User::whereIn('usu_cedula', $cedulas)->get()
                        ->each(fn ($u) => $creadorNombres[trim((string) $u->usu_cedula)] = trim((string) $u->usu_cedula));
                }
            } catch (\Throwable $e) {
                Log::warning('Error cargando nombres de creadores: ' . $e->getMessage());
            }
        }

        // Paginación manual
        $perPage = 10;
        $page = (int) $request->get('page', 1);
        $total = $lista->count();
        $items = $lista->slice(($page - 1) * $perPage, $perPage)->values();

        $esAdmin = $activeRole === 'administrador';

        return view('grupos_proyecto.index', compact(
            'items', 'total', 'perPage', 'page',
            'lapsos', 'programas', 'secciones',
            'filterLapso', 'filterPrograma', 'filterSeccion', 'search',
            'tablaOk', 'isProfessor', 'esAdmin', 'esCoordinador', 'proyectoPorClave', 'creadorNombres',
        ))->with('userCedula', trim((string) $user->usu_cedula))
          ->with('userUsuNombre', trim((string) $user->usu_nombre));
    }

    /**
     * Show the form for creating a new group.
     */
    public function create()
    {
        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);
        $isProfessor = $activeRole === 'profesor proyecto';

        // Solo profesor proyecto puede crear grupos
        if ($activeRole !== 'profesor proyecto') {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'Solo el profesor de proyecto puede crear grupos.');
        }

        Log::info('create: rol='.($activeRole ?? 'null').', isProfessor='.($isProfessor ? 'true' : 'false').', cedula='.$user->usu_cedula);

        $tablaOk = $this->grupos->tablaDisponible();
        // Solo profesor proyecto puede crear grupos, solo ve lapsos activos
        $lapsos = $this->profesores->lapsosActivos();

        // Comunidades para el select
        $comunidades = Cache::remember('grupos_comunidades_form', 3600, fn () =>
            Comunidad::query()->orderBy('nombre')->get(['com_codigo', 'com_nombre', 'com_rif'])
        );

        // Estados para modal de comunidad
        $estados = Estado::orderBy('est_nombre')->get();

        // Si es profesor, pre-seleccionar lapso vigente
        $lapsoPreseleccionado = '';
        if ($isProfessor) {
            $lapsoVigente = $this->profesores->lapsoVigenteCodigo();
            if ($lapsoVigente) {
                $lapsoPreseleccionado = (string) $lapsoVigente;
                Log::info('create: lapsoVigente='.$lapsoPreseleccionado);
            } else {
                Log::warning('create: lapsoVigenteCodigo returned null');
            }
        } else {
            Log::warning('create: isProfessor=false, no se preselecciona lapso');
        }

        return view('grupos_proyecto.form', compact(
            'lapsos', 'comunidades', 'estados',
            'tablaOk', 'isProfessor', 'lapsoPreseleccionado',
        ))->with('grupo', null);
    }

    /**
     * Store a newly created group.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);

        // Solo profesor proyecto puede crear grupos
        if ($activeRole !== 'profesor proyecto') {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'Solo el profesor de proyecto puede crear grupos.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:120',
            'lapso' => 'required|integer|min:1',
            'programa' => 'nullable|integer',
            'seccion' => 'required|integer|min:1',
            'comunidad' => 'required|integer|min:1',
            'miembros' => 'required|string', // JSON string of members
        ], [
            'nombre.required' => 'Indique un nombre para el equipo.',
            'lapso.required' => 'Seleccione el lapso académico.',
            'programa.integer' => 'El programa seleccionado no es válido.',
            'seccion.required' => 'Seleccione la sección del PNF.',
            'comunidad.required' => 'Seleccione la comunidad.',
            'miembros.required' => 'Debe agregar al menos un integrante al grupo.',
        ], [
            'nombre' => 'nombre del equipo',
            'lapso' => 'lapso académico',
            'programa' => 'programa',
            'seccion' => 'sección',
            'comunidad' => 'comunidad',
            'miembros' => 'integrantes',
        ]);

        $lapCodigo = (int) $request->input('lapso');
        $secCodigo = (int) $request->input('seccion');
        $proCodigo = $request->input('programa') ? (int) $request->input('programa') : null;
        $comCodigo = (int) $request->input('comunidad');
        $nombre = trim($request->input('nombre'));

        // Parsear miembros desde JSON
        $miembros = json_decode($request->input('miembros'), true);
        if (! is_array($miembros) || count($miembros) === 0) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe agregar al menos un integrante al grupo.');
        }

        // Validar que haya al menos un líder
        $tieneLider = false;
        foreach ($miembros as $m) {
            if ((int) ($m['rol_id'] ?? 0) === IntranetEquipoSeccionService::ROL_LIDER) {
                $tieneLider = true;
                break;
            }
        }
        if (! $tieneLider) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe haber al menos un integrante con rol de Líder.');
        }

        // Validar que los estudiantes no estén ya en otro grupo en el mismo lapso
        foreach ($miembros as $m) {
            $cedula = trim($m['cedula'] ?? '');
            if ($cedula !== '' && $this->grupos->estudianteEnGrupoEnLapso($cedula, $lapCodigo)) {
                return redirect()->back()->withInput()
                    ->with('error', "El estudiante {$cedula} ya pertenece a un grupo en este lapso académico.");
            }
        }

        if (! $this->grupos->tablaDisponible()) {
            return redirect()->back()->withInput()
                ->with('error', 'Ejecute la migración grupo_proyecto_modulo en repositorio (solo módulo).');
        }

        // Obtener etiquetas de contexto académico
        $etiquetas = $this->equipos->etiquetasContexto($lapCodigo, $secCodigo, $proCodigo);

        $user = auth()->user();
        $clave = $this->grupos->registrar(
            $nombre,
            $lapCodigo,
            $secCodigo,
            $proCodigo,
            $comCodigo,
            $miembros,
            trim((string) $user->usu_cedula),
            null, // grpCodigo = null (nuevo)
            $etiquetas,
            trim((string) $user->usu_nombre),
        );

        if (! $clave) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe incluir al menos un integrante y un líder, o el grupo pudo no haberse creado correctamente.');
        }

        try {
            $cedulas = collect($miembros)->pluck('cedula')->filter()->values()->toArray();
            $this->notificacionService->notificarNuevoGrupo($nombre, trim((string) $user->usu_cedula), $cedulas);
        } catch (\Throwable $e) {
            Log::warning("Error notificando nuevo grupo: " . $e->getMessage());
        }

        // ─── Recordatorio: estudiantes de la misma sección sin equipo ───
        $mensajeAdicional = '';
        try {
            $claveSeccion = $this->equipos->construirClave($lapCodigo, $secCodigo);
            $estudiantesSeccion = $this->equipos->integrantes($claveSeccion);
            $cedulasOcupadas = $this->grupos->cedulasOcupadasEnLapso($lapCodigo);
            $cedulasOcupadasIndex = array_flip($cedulasOcupadas);

            $sinGrupo = 0;
            foreach ($estudiantesSeccion as $est) {
                $c = trim($est->cedula ?? '');
                if ($c !== '' && !isset($cedulasOcupadasIndex[$c])) {
                    $sinGrupo++;
                }
            }

            if ($sinGrupo > 0) {
                $secNombre = $etiquetas['sec_nombre'] ?? $secCodigo;
                $mensajeAdicional = " Además, {$sinGrupo} estudiante(s) de la sección {$secNombre} aún no están en ningún equipo.";
            }
        } catch (\Throwable $e) {
            Log::warning('Error calculando estudiantes sin grupo: ' . $e->getMessage());
        }

        return redirect()->route('grupos-proyecto.index')
            ->with('success', 'Grupo registrado correctamente.' . $mensajeAdicional);
    }

    /**
     * Show the form for editing a group.
     */
    public function edit($id)
    {
        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);
        $isProfessor = $activeRole === 'profesor proyecto';

        // Solo profesor proyecto puede editar grupos
        if ($activeRole !== 'profesor proyecto') {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'Solo el profesor de proyecto puede editar grupos.');
        }

        $tablaOk = $this->grupos->tablaDisponible();
        $lapsos = $this->profesores->lapsosActivos();

        $grupo = $this->grupos->obtener((int) $id);
        if (! $grupo) {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'Grupo no encontrado.');
        }

        // Solo el creador (identificado por usu_nombre) puede editar
        $creadorUsu = trim((string) ($grupo->creador_usuario ?? ''));
        $usuNombre = trim((string) $user->usu_nombre);
        if ($creadorUsu !== '' && $creadorUsu !== $usuNombre) {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'No tienes permiso para editar este grupo.');
        }

        // Bloquear si el proyecto asociado ya está aprobado
        $proyecto = $this->proyectoRepo->findFirstByEquipoRef($grupo->clave);
        if ($proyecto && $proyecto->estado_validacion === 'aprobado') {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'No se puede editar el grupo porque el proyecto asociado ya está aprobado.');
        }

        // Comunidades
        $comunidades = Cache::remember('grupos_comunidades_form', 3600, fn () =>
            Comunidad::query()->orderBy('nombre')->get(['com_codigo', 'com_nombre', 'com_rif'])
        );

        // Estados para modal de comunidad
        $estados = Estado::orderBy('est_nombre')->get();

        $lapsoPreseleccionado = (string) $grupo->lap_codigo;

        return view('grupos_proyecto.form', compact(
            'grupo', 'lapsos', 'comunidades', 'estados',
            'tablaOk', 'isProfessor', 'lapsoPreseleccionado',
        ));
    }

    /**
     * Update an existing group.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);

        // Solo profesor proyecto puede actualizar grupos
        if ($activeRole !== 'profesor proyecto') {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'Solo el profesor de proyecto puede actualizar grupos.');
        }

        $grpCodigo = (int) $id;
        $grupo = $this->grupos->obtener($grpCodigo);
        if (! $grupo) {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'Grupo no encontrado.');
        }

        // Solo el creador (identificado por usu_nombre) puede actualizar
        $creadorUsu = trim((string) ($grupo->creador_usuario ?? ''));
        $usuNombre = trim((string) $user->usu_nombre);
        if ($creadorUsu !== '' && $creadorUsu !== $usuNombre) {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'No tienes permiso para actualizar este grupo.');
        }

        // Bloquear si el proyecto asociado ya está aprobado
        $proyecto = $this->proyectoRepo->findFirstByEquipoRef($grupo->clave);
        if ($proyecto && $proyecto->estado_validacion === 'aprobado') {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'No se puede actualizar el grupo porque el proyecto asociado ya está aprobado.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:120',
            'lapso' => 'required|integer|min:1',
            'programa' => 'nullable|integer',
            'seccion' => 'required|integer|min:1',
            'comunidad' => 'required|integer|min:1',
            'miembros' => 'required|string',
        ], [
            'nombre.required' => 'Indique un nombre para el equipo/grupo.',
            'lapso.required' => 'Seleccione el lapso académico.',
            'programa.integer' => 'El programa seleccionado no es válido.',
            'seccion.required' => 'Seleccione la sección del PNF.',
            'comunidad.required' => 'Seleccione la comunidad.',
            'miembros.required' => 'Debe agregar al menos un integrante al grupo.',
        ], [
            'nombre' => 'nombre del equipo',
            'lapso' => 'lapso académico',
            'programa' => 'programa',
            'seccion' => 'sección',
            'comunidad' => 'comunidad',
            'miembros' => 'integrantes',
        ]);

        $lapCodigo = (int) $request->input('lapso');
        $nombre = trim($request->input('nombre'));
        $secCodigo = (int) $request->input('seccion');
        $proCodigo = $request->input('programa') ? (int) $request->input('programa') : null;
        $comCodigo = (int) $request->input('comunidad');

        // Validar unicidad global del nombre (excluyendo este grupo)
        if (! $this->grupos->nombreDisponibleEnLapso($nombre, null, $grpCodigo)) {
            return redirect()->back()->withInput()
                ->withErrors(['nombre' => 'Este nombre de grupo ya está en uso.']);
        }

        $miembros = json_decode($request->input('miembros'), true);
        if (! is_array($miembros) || count($miembros) === 0) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe agregar al menos un integrante al grupo.');
        }

        $tieneLider = false;
        foreach ($miembros as $m) {
            if ((int) ($m['rol_id'] ?? 0) === IntranetEquipoSeccionService::ROL_LIDER) {
                $tieneLider = true;
                break;
            }
        }
        if (! $tieneLider) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe haber al menos un integrante con rol de Líder.');
        }

        // Validar que los estudiantes no estén en otro grupo (excluyendo este)
        foreach ($miembros as $m) {
            $cedula = trim($m['cedula'] ?? '');
            if ($cedula !== '' && $this->grupos->estudianteEnGrupoEnLapso($cedula, $lapCodigo, $grpCodigo)) {
                return redirect()->back()->withInput()
                    ->with('error', "El estudiante {$cedula} ya pertenece a un grupo en este lapso académico.");
            }
        }

        if (! $this->grupos->tablaDisponible()) {
            return redirect()->back()->withInput()
                ->with('error', 'Ejecute la migración grupo_proyecto_modulo en repositorio (solo módulo).');
        }

        $etiquetas = $this->equipos->etiquetasContexto($lapCodigo, $secCodigo, $proCodigo);

        $user = auth()->user();
        $clave = $this->grupos->registrar(
            $nombre,
            $lapCodigo,
            $secCodigo,
            $proCodigo,
            $comCodigo,
            $miembros,
            trim((string) $user->usu_cedula),
            $grpCodigo,
            $etiquetas,
            trim((string) $user->usu_nombre),
        );

        if (! $clave) {
            return redirect()->back()->withInput()
                ->with('error', 'No se pudo actualizar el grupo. Verifique los datos.');
        }

        return redirect()->route('grupos-proyecto.index')
            ->with('success', 'Grupo actualizado correctamente.');
    }

    public function destroy(Request $request, $id)
    {
        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);

        // Solo profesor proyecto puede eliminar grupos
        if ($activeRole !== 'profesor proyecto') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Solo el profesor de proyecto puede eliminar grupos.']);
            }
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'Solo el profesor de proyecto puede eliminar grupos.');
        }

        $grpCodigo = (int) $id;
        $grupo = $this->grupos->obtener($grpCodigo);

        if (! $grupo) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Grupo no encontrado.']);
            }
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'Grupo no encontrado.');
        }

        // Solo el creador (identificado por usu_nombre) puede eliminar
        $creadorUsu = trim((string) ($grupo->creador_usuario ?? ''));
        $usuNombre = trim((string) $user->usu_nombre);
        if ($creadorUsu !== '' && $creadorUsu !== $usuNombre) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar este grupo.']);
            }
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'No tienes permiso para eliminar este grupo.');
        }

        $proyecto = $this->proyectoRepo->findFirstByEquipoRef($grupo->clave);
        if ($proyecto && $proyecto->estado_validacion === 'aprobado') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar el grupo porque el proyecto asociado ya está aprobado.']);
            }
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'No se puede eliminar el grupo porque el proyecto asociado ya está aprobado.');
        }

        $ok = $this->grupos->eliminar($grpCodigo);

        if ($request->ajax() || $request->wantsJson()) {
            if ($ok) {
                return response()->json(['success' => true, 'message' => 'Grupo eliminado correctamente.']);
            }
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar el grupo. Verifique la conexión con la base de datos.']);
        }

        if ($ok) {
            return redirect()->route('grupos-proyecto.index')
                ->with('success', 'Grupo eliminado correctamente.');
        }

        return redirect()->route('grupos-proyecto.index')
            ->with('error', 'No se pudo eliminar el grupo. Verifique la conexión con la base de datos.');
    }

    // ====== API JSON endpoints for cascading selects ======

    /**
     * Get programas for a given lapso (JSON).
     */
    public function getProgramas($lapso)
    {
        $lapCodigo = (int) $lapso;
        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);
        $todosProgramas = $this->equipos->programasEnLapso($lapCodigo);

        if ($activeRole === 'profesor proyecto') {
            $proCodigos = $this->profesores->programasDelDocente(
                trim((string) $user->usu_cedula),
                $lapCodigo,
            );
            if ($proCodigos !== []) {
                $filtrados = $todosProgramas->whereIn('pro_codigo', $proCodigos)->values();
                if ($filtrados->isNotEmpty()) {
                    return response()->json($filtrados);
                }
                Log::warning('getProgramas: profesor filtro vacío (proCodigos='.json_encode($proCodigos).'), fallback a todos');
            }
        }

        return response()->json($todosProgramas);
    }

    /**
     * Get trayectos for a given lapso and programa (JSON).
     */
    public function getTrayectos($lapso, $programa)
    {
        $lapCodigo = (int) $lapso;
        $proCodigo = $programa !== null && $programa !== '' ? (int) $programa : null;
        $trayectos = $this->equipos->trayectosEnLapso($lapCodigo, $proCodigo);
        return response()->json($trayectos);
    }

    /**
     * Get secciones for a given lapso, programa and optionally trayecto (JSON).
     */
    public function getSecciones($lapso, $programa = null)
    {
        $lapCodigo = (int) $lapso;
        $proCodigo = $programa !== null && $programa !== '' ? (int) $programa : null;
        $trayectoCodigo = request()->get('trayecto') ? (int) request()->get('trayecto') : null;
        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);
        $todasSecciones = $this->equipos->seccionesEnLapso($lapCodigo, $proCodigo);

        if ($activeRole === 'profesor proyecto') {
            $secCodigos = $this->profesores->seccionesDelDocente(
                trim((string) $user->usu_cedula),
                $lapCodigo,
            );
            if ($secCodigos !== []) {
                $filtradas = $todasSecciones->whereIn('sec_codigo', $secCodigos)->values();
                if ($filtradas->isNotEmpty()) {
                    if ($trayectoCodigo) {
                        $filtradas = $filtradas->where('tra_codigo', $trayectoCodigo)->values();
                    }
                    return response()->json($filtradas);
                }
                Log::warning('getSecciones: profesor filtro vacío (secCodigos='.json_encode($secCodigos).'), fallback');
            }
        }

        if ($trayectoCodigo) {
            $todasSecciones = $todasSecciones->where('tra_codigo', $trayectoCodigo)->values();
        }

        return response()->json($todasSecciones);
    }

    /**
     * Get estudiantes (candidates) for a given lapso and seccion (JSON).
     */
    public function getEstudiantes(Request $request, $lapso, $seccion)
    {
        $lapCodigo = (int) $lapso;
        $secCodigo = (int) $seccion;

        $candidatos = $this->grupos->candidatosSeccion($lapCodigo, $secCodigo);

        // Filtrar estudiantes que ya están en otros grupos en este lapso
        $excludeGrp = $request->get('exclude_grp') ? (int) $request->get('exclude_grp') : null;
        $ocupadas = $this->grupos->cedulasOcupadasEnLapso($lapCodigo, $excludeGrp);
        $ocupadasIndex = array_flip($ocupadas);

        $candidatos = $candidatos->reject(fn ($est) => isset($ocupadasIndex[trim($est->cedula ?? '')]));

        return response()->json($candidatos->values());
    }

    /**
     * AJAX: buscar grupos por nombre, código o identificador (autocompletado).
     */
    public function buscarAjax(Request $request)
    {
        $search = trim($request->query('q', ''));
        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $term = '%' . mb_strtolower($search) . '%';

        $grupos = GrupoProyectoModulo::where('estado_logico', true)
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(grp_nombre) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(grp_identificador) LIKE ?', [$term]);
            })
            ->orderByDesc('grp_codigo')
            ->limit(10)
            ->get(['grp_codigo', 'grp_nombre', 'grp_identificador', 'grp_contexto']);

        $results = [];
        foreach ($grupos as $g) {
            $results[] = [
                'id'    => $g->grp_codigo,
                'title' => $g->grp_nombre,
                'code'  => $g->grp_identificador ?? '',
            ];
        }

        return response()->json($results);
    }

    /**
     * Check if a group name is available globally (for real-time validation).
     */
    public function checkNombreDisponible(Request $request)
    {
        $nombreLimpio = $request->get('nombre', '');

        $grpCodigo = $request->get('exclude');
        $excludeId = $grpCodigo ? (int) $grpCodigo : null;

        $available = $this->grupos->nombreDisponibleEnLapso($nombreLimpio, null, $excludeId);

        return response()->json(['available' => $available]);
    }

    /**
     * AJAX endpoint to create a community from within the group form modal.
     */
    public function crearComunidadAjax(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'rif_letra' => 'nullable|string|max:1',
            'rif_numero' => 'nullable|string|max:9',
            'correo' => 'nullable|email|max:150',
            'prefijo_telefono' => 'nullable|string|max:4',
            'numero_telefono' => 'nullable|string|max:15',
            'estado_id' => 'required|integer|exists:estados,est_codigo',
            'municipio_id' => 'required|integer|exists:municipios,mun_codigo',
            'dir_nombre' => 'required|string|max:500',
        ], [
            'nombre.required' => 'El nombre de la comunidad es obligatorio.',
            'estado_id.required' => 'Seleccione un estado.',
            'municipio_id.required' => 'Seleccione un municipio.',
            'dir_nombre.required' => 'La dirección exacta es obligatoria.',
        ], [
            'nombre' => 'nombre de la comunidad',
            'rif_letra' => 'letra del RIF',
            'rif_numero' => 'número del RIF',
            'correo' => 'correo electrónico',
            'prefijo_telefono' => 'prefijo telefónico',
            'numero_telefono' => 'número telefónico',
            'estado_id' => 'estado',
            'municipio_id' => 'municipio',
            'dir_nombre' => 'dirección',
        ]);

        // Validar unicidad del nombre
        $nombreStatus = app(UnicidadNombreService::class)->check(
            Comunidad::class,
            'nombre',
            $request->input('nombre'),
        );
        if (! $nombreStatus) {
            return response()->json(['ok' => false, 'error' => 'Este nombre de comunidad ya está en uso.'], 422);
        }

        // Validar RIF si se proporcionó
        $rifCompleto = null;
        $rifNumero = $request->input('rif_numero', '');
        if ($rifNumero !== '' && strlen($rifNumero) >= 9) {
            $rifLetra = $request->input('rif_letra', 'J');
            $rifService = app(ValidacionRifService::class);
            $digito = $rifService->calcularDigito($rifLetra, $rifNumero);
            if ($digito === null) {
                return response()->json(['ok' => false, 'error' => 'El RIF ingresado no es válido.'], 422);
            }
            $rifCompleto = "{$rifLetra}-{$rifNumero}-{$digito}";
        }

        // Validar correo si se proporcionó
        $correo = $request->input('correo', '');
        if ($correo !== '') {
            $correoService = app(ValidacionCorreoService::class);
            $resultado = $correoService->validarCompleto($correo, true);
            if (! $resultado['valido']) {
                return response()->json(['ok' => false, 'error' => $resultado['error'] ?? 'El correo ingresado no es válido.'], 422);
            }
        }

        $gestion = app(ComunidadGestionService::class);
        $payload = [
            'nombre' => $request->input('nombre'),
            'correo' => $correo ?: null,
            'prefijo_telefono' => $request->input('prefijo_telefono', ''),
            'numero_telefono' => $request->input('numero_telefono', ''),
            'estado_id' => $request->input('estado_id'),
            'municipio_id' => $request->input('municipio_id'),
            'dir_nombre' => $request->input('dir_nombre'),
        ];
        if ($rifCompleto) {
            $payload['rif'] = $rifCompleto;
        }

        $id = $gestion->guardar(null, $payload);

        Cache::forget('grupos_comunidades_form');
        $comunidad = Comunidad::find($id);

        return response()->json([
            'ok' => true,
            'id' => $id,
            'nombre' => $comunidad ? $comunidad->nombre : $request->input('nombre'),
        ]);
    }
}
