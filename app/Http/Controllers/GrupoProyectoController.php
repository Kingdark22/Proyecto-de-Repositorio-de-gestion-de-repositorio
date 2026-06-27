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
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);
        $isProfessor = $activeRole === 'profesor proyecto';

        $tablaOk = $this->grupos->tablaDisponible();

        // Lapsos
        $lapsos = $this->profesores->lapsosActivos();

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
            $filters['creador'] = trim((string) $user->usu_cedula);
            $secCodigos = $this->profesores->seccionesDelDocente(
                trim((string) $user->usu_cedula),
                $lapCodigo,
            );
            $filters['seccion'] = $secCodigos !== [] ? $secCodigos : [-1];
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

        // Paginación manual
        $perPage = 10;
        $page = (int) $request->get('page', 1);
        $total = $lista->count();
        $items = $lista->slice(($page - 1) * $perPage, $perPage)->values();

        return view('grupos_proyecto.index', compact(
            'items', 'total', 'perPage', 'page',
            'lapsos', 'programas', 'secciones',
            'filterLapso', 'filterPrograma', 'filterSeccion', 'search',
            'tablaOk', 'isProfessor', 'proyectoPorClave',
        ));
    }

    /**
     * Show the form for creating a new group.
     */
    public function create()
    {
        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);
        $isProfessor = $activeRole === 'profesor proyecto';

        Log::info('create: rol='.($activeRole ?? 'null').', isProfessor='.($isProfessor ? 'true' : 'false').', cedula='.$user->usu_cedula);

        $tablaOk = $this->grupos->tablaDisponible();
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
        $validated = $request->validate([
            'nombre' => 'required|string|max:120',
            'lapso' => 'required|integer|min:1',
            'programa' => 'nullable|integer',
            'seccion' => 'required|integer|min:1',
            'comunidad' => 'required|integer|min:1',
            'miembros' => 'required|string', // JSON string of members
        ], [
            'nombre.required' => 'Indique un nombre para el equipo/grupo.',
            'lapso.required' => 'Seleccione el lapso académico.',
            'seccion.required' => 'Seleccione la sección del PNF.',
            'comunidad.required' => 'Seleccione la comunidad.',
            'miembros.required' => 'Debe agregar al menos un integrante al grupo.',
        ]);

        $lapCodigo = (int) $request->input('lapso');
        $nombre = trim($request->input('nombre'));
        $secCodigo = (int) $request->input('seccion');
        $proCodigo = $request->input('programa') ? (int) $request->input('programa') : null;
        $comCodigo = (int) $request->input('comunidad');

        // Validar unicidad del nombre en el lapso
        if (! $this->grupos->nombreDisponibleEnLapso($nombre, $lapCodigo)) {
            return redirect()->back()->withInput()
                ->withErrors(['nombre' => 'Este nombre de grupo ya está en uso en el lapso académico seleccionado.']);
        }

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
        );

        if (! $clave) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe incluir al menos un integrante y un líder, o el grupo pudo no haberse creado correctamente.');
        }

        $nombresMiembros = collect($miembros)
            ->map(fn($m) => trim(($m['nombre'] ?? '') . ' ' . ($m['apellido'] ?? '')))
            ->filter()
            ->implode(', ');

        return redirect()->route('grupos-proyecto.index')
            ->with('success', 'Grupo registrado. Clave: ' . $clave . '. Integrantes: ' . $nombresMiembros);
    }

    /**
     * Show the form for editing a group.
     */
    public function edit($id)
    {
        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);
        $isProfessor = $activeRole === 'profesor proyecto';

        $tablaOk = $this->grupos->tablaDisponible();
        $lapsos = $this->profesores->lapsosActivos();

        $grupo = $this->grupos->obtener((int) $id);
        if (! $grupo) {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'Grupo no encontrado.');
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
        $grpCodigo = (int) $id;
        $grupo = $this->grupos->obtener($grpCodigo);
        if (! $grupo) {
            return redirect()->route('grupos-proyecto.index')
                ->with('error', 'Grupo no encontrado.');
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
            'seccion.required' => 'Seleccione la sección del PNF.',
            'comunidad.required' => 'Seleccione la comunidad.',
            'miembros.required' => 'Debe agregar al menos un integrante al grupo.',
        ]);

        $lapCodigo = (int) $request->input('lapso');
        $nombre = trim($request->input('nombre'));
        $secCodigo = (int) $request->input('seccion');
        $proCodigo = $request->input('programa') ? (int) $request->input('programa') : null;
        $comCodigo = (int) $request->input('comunidad');

        // Validar unicidad del nombre (excluyendo este grupo)
        if (! $this->grupos->nombreDisponibleEnLapso($nombre, $lapCodigo, $grpCodigo)) {
            return redirect()->back()->withInput()
                ->withErrors(['nombre' => 'Este nombre de grupo ya está en uso en el lapso académico seleccionado.']);
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
        );

        if (! $clave) {
            return redirect()->back()->withInput()
                ->with('error', 'No se pudo actualizar el grupo. Verifique los datos.');
        }

        return redirect()->route('grupos-proyecto.index')
            ->with('success', 'Grupo actualizado correctamente. Clave: ' . $clave);
    }

    public function destroy($id)
    {
        $this->grupos->eliminar((int) $id);
        return redirect()->route('grupos-proyecto.index')
            ->with('success', 'Grupo eliminado correctamente.');
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
    public function getEstudiantes($lapso, $seccion)
    {
        $lapCodigo = (int) $lapso;
        $secCodigo = (int) $seccion;

        $candidatos = $this->grupos->candidatosSeccion($lapCodigo, $secCodigo);

        return response()->json($candidatos);
    }

    /**
     * Check if a group name is available in a given lapso (for real-time validation).
     */
    public function checkNombreDisponible($lapso, $nombre)
    {
        $lapCodigo = (int) $lapso;
        $nombreLimpio = $nombre; // Laravel ya decodifica URL parameters

        $grpCodigo = request()->get('exclude');
        $excludeId = $grpCodigo ? (int) $grpCodigo : null;

        $available = $this->grupos->nombreDisponibleEnLapso($nombreLimpio, $lapCodigo, $excludeId);

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
