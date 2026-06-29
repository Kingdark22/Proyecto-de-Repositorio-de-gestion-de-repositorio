<?php

namespace App\Http\Controllers;

use App\Models\Componente;
use App\Models\ComponentePrograma;
use App\Repositories\CatalogoRepository;
use App\Services\UnicidadNombreService;
use Illuminate\Http\Request;

class ComponenteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $query = Componente::query();

        if ($search !== '') {
            $query->where('nombre', 'ILIKE', $search . '%');
        }

        $listaRegistros = $query->with('programas')->latest('id')->paginate(10);
        $programasDisponibles = app(CatalogoRepository::class)->programasDisponibles();

        return view('componentes.index', compact('listaRegistros', 'search', 'programasDisponibles'));
    }

    public function create()
    {
        return view('componentes.create');
    }

    public function store(Request $request, UnicidadNombreService $unicidadService)
    {
        $request->validate([
            'nombre' => 'required|string|min:3|max:100',
            'tipo_archivo' => 'required|string|max:100',
            'tamano_maximo_mb' => 'required|integer|min:1|max:200',
            'es_obligatorio' => 'boolean',
        ], [
            'nombre.required' => 'El nombre del componente es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'tipo_archivo.required' => 'El tipo de archivo es obligatorio.',
            'tipo_archivo.max' => 'El tipo de archivo no puede exceder 100 caracteres.',
            'tamano_maximo_mb.required' => 'El tamaño máximo es obligatorio.',
            'tamano_maximo_mb.integer' => 'El tamaño máximo debe ser un número entero.',
            'tamano_maximo_mb.min' => 'El tamaño máximo debe ser al menos 1 MB.',
            'tamano_maximo_mb.max' => 'El tamaño máximo no puede exceder 200 MB.',
        ]);

        $nombre = trim($request->input('nombre'));

        $disponible = $unicidadService->check(
            Componente::class,
            'nombre',
            $nombre,
            null,
        );

        if (!$disponible) {
            return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
        }

        Componente::create([
            'nombre' => $nombre,
            'es_obligatorio' => $request->boolean('es_obligatorio', true),
            'tipo_archivo' => $request->input('tipo_archivo', 'pdf'),
            'tamano_maximo_mb' => $request->input('tamano_maximo_mb', 10),
            'estado_logico' => true,
        ]);

        return redirect()->route('componentes.index')
            ->with('success', 'Componente creado con éxito.');
    }

    public function edit($id)
    {
        $item = Componente::findOrFail($id);
        return view('componentes.edit', compact('item'));
    }

    public function update(Request $request, $id, UnicidadNombreService $unicidadService)
    {
        $request->validate([
            'nombre' => 'required|string|min:3|max:100',
            'tipo_archivo' => 'required|string|max:100',
            'tamano_maximo_mb' => 'required|integer|min:1|max:200',
            'es_obligatorio' => 'boolean',
        ], [
            'nombre.required' => 'El nombre del componente es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'tipo_archivo.required' => 'El tipo de archivo es obligatorio.',
            'tipo_archivo.max' => 'El tipo de archivo no puede exceder 100 caracteres.',
            'tamano_maximo_mb.required' => 'El tamaño máximo es obligatorio.',
            'tamano_maximo_mb.integer' => 'El tamaño máximo debe ser un número entero.',
            'tamano_maximo_mb.min' => 'El tamaño máximo debe ser al menos 1 MB.',
            'tamano_maximo_mb.max' => 'El tamaño máximo no puede exceder 200 MB.',
        ]);

        $item = Componente::findOrFail($id);
        $nombre = trim($request->input('nombre'));

        if ($item->nombre !== $nombre) {
            $disponible = $unicidadService->check(
                Componente::class,
                'nombre',
                $nombre,
                $id,
            );
            if (!$disponible) {
                return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
            }
        }

        $item->update([
            'nombre' => $nombre,
            'es_obligatorio' => $request->boolean('es_obligatorio', true),
            'tipo_archivo' => $request->input('tipo_archivo', 'pdf'),
            'tamano_maximo_mb' => $request->input('tamano_maximo_mb', 10),
        ]);

        return redirect()->route('componentes.index')
            ->with('success', 'Componente actualizado con éxito.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $item = Componente::findOrFail($id);
        $nuevoEstado = !$item->estado_logico;
        $item->update(['estado_logico' => $nuevoEstado]);

        $mensaje = $nuevoEstado
            ? 'Componente activado correctamente.'
            : 'Componente suspendido correctamente.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'nuevo_estado' => $nuevoEstado,
            ]);
        }

        return redirect()->route('componentes.index')
            ->with('success', $mensaje);
    }

    public function destroy(Request $request, $id)
    {
        $item = Componente::findOrFail($id);
        ComponentePrograma::where('comp_codigo', $item->comp_codigo)->delete();
        $item->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Componente eliminado correctamente.']);
        }

        return redirect()->route('componentes.index')
            ->with('success', 'Componente eliminado correctamente.');
    }

    // ── Vinculación Componente → PNF + Trayectos (MVC) ──

    /**
     * Página de vinculación múltiple: seleccionar componentes y vincularlos a PNF/Trayectos.
     */
    public function vinculacionGlobal()
    {
        $catalogoRepo = app(CatalogoRepository::class);

        // Todos los componentes activos
        $componentes = Componente::query()->where('estado_logico', true)->orderBy('nombre')->get();

        $programas = $catalogoRepo->programasDisponibles();
        $trayectos = $catalogoRepo->trayectosPorPrograma(0);

        // Cargar asignaciones existentes de todos los componentes
        $compIds = $componentes->pluck('id')->toArray();
        $asignaciones = ComponentePrograma::whereIn('comp_codigo', $compIds)->get();

        $trayectosDisponibles = $trayectos->toArray();
        $pnfRows = [];

        foreach ($programas as $prog) {
            $proCodigo = (int) $prog->pro_codigo;
            $trayectosData = [];

            // Verificar si algún componente tiene este PNF asignado
            $asigsEstePnf = $asignaciones->where('pro_codigo', $proCodigo);

            foreach ($trayectos as $tra) {
                $traCodigo = (string) $tra->tra_codigo;
                $asig = $asigsEstePnf->firstWhere('tra_codigo', $traCodigo);
                $trayectosData[$traCodigo] = [
                    'nombre' => $tra->tra_nombre ?? $traCodigo,
                    'selected' => $asig !== null,
                ];
            }

            $pnfRows[$proCodigo] = [
                'pro_codigo' => $proCodigo,
                'pro_siglas' => $prog->pro_siglas ?? $prog->pro_nombre,
                'activo' => $asigsEstePnf->isNotEmpty(),
                'trayectos' => $trayectosData,
            ];
        }

        return view('componentes.vinculacion_global', compact(
            'componentes',
            'pnfRows',
            'trayectosDisponibles',
            'asignaciones'
        ));
    }

    /**
     * Guardar vinculaciones de uno o más componentes a PNF/Trayectos.
     */
    public function vinculacionStore(Request $request)
    {
        $request->validate([
            'componente_ids' => 'required|array|min:1',
            'componente_ids.*' => 'required|integer|min:1|exists:componentes,comp_codigo',
        ]);

        $componenteIds = $request->input('componente_ids', []);

        // Procesar PNFs activos
        $pnfsActivos = $request->input('pnf_activo', []);
        $trayectosSelected = $request->input('tra_selected', []);

        $totalVinculaciones = 0;

        foreach ($componenteIds as $compCodigo) {
            $compCodigo = (int) $compCodigo;

            // Eliminar asignaciones existentes de este componente
            ComponentePrograma::where('comp_codigo', $compCodigo)->delete();

            foreach ($pnfsActivos as $proCodigo => $activo) {
                if ((int) $activo !== 1) continue;
                $proCodigo = (int) $proCodigo;

                $trayectos = $trayectosSelected[$proCodigo] ?? [];
                foreach ($trayectos as $traCodigo => $selected) {
                    if ((int) $selected !== 1) continue;

                    ComponentePrograma::create([
                        'comp_codigo' => $compCodigo,
                        'pro_codigo' => $proCodigo,
                        'tra_codigo' => (string) $traCodigo,
                    ]);
                    $totalVinculaciones++;
                }
            }
        }

        if ($totalVinculaciones === 0) {
            return redirect()->route('componentes.vinculacion')
                ->with('error', 'No se realizaron vinculaciones. Debe seleccionar al menos un PNF y un trayecto.');
        }

        $numComponentes = count($componenteIds);
        return redirect()->route('componentes.index')
            ->with('success', "Vinculación guardada: {$totalVinculaciones} registro(s) creado(s) para {$numComponentes} componente(s).");
    }
}
