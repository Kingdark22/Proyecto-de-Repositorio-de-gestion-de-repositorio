<?php

namespace App\Http\Controllers;

use App\Helpers\DualDatabase;
use App\Models\LineaInvestigacion;
use App\Services\UnicidadNombreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LineaInvestigacionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $items = LineaInvestigacion::where('nombre_investigacion', 'ILIKE', '%' . $search . '%')
            ->latest()
            ->paginate(10);

        return view('lineas.index', compact('items', 'search'));
    }

    public function create()
    {
        $programas = $this->getProgramas();
        return view('lineas.create', compact('programas'));
    }

    public function store(Request $request, UnicidadNombreService $unicidadService)
    {
        $validated = $request->validate([
            'nombre_investigacion' => 'required|min:3|max:100',
            'area_de_investigacion' => 'required|min:3|max:100',
            'programa_id' => 'required|integer',
            'descripcion' => 'required|min:3|max:500',
        ], [
            'nombre_investigacion.required' => 'El nombre de la línea es obligatorio.',
            'nombre_investigacion.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre_investigacion.max' => 'El nombre no puede exceder 100 caracteres.',
            'area_de_investigacion.required' => 'El área de investigación es obligatoria.',
            'area_de_investigacion.min' => 'El área debe tener al menos 3 caracteres.',
            'area_de_investigacion.max' => 'El área no puede exceder 100 caracteres.',
            'programa_id.required' => 'Debe seleccionar un programa.',
            'programa_id.integer' => 'El programa seleccionado no es válido.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 3 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
        ]);

        $nombre = trim($validated['nombre_investigacion']);

        $disponible = $unicidadService->check(
            LineaInvestigacion::class,
            'nombre_investigacion',
            $nombre,
            null,
        );

        if (!$disponible) {
            return back()->withErrors(['nombre_investigacion' => 'Este nombre ya está en uso.'])->withInput();
        }

        LineaInvestigacion::guardar([
            'nombre_investigacion' => $nombre,
            'area_de_investigacion' => trim($validated['area_de_investigacion']),
            'programa_id' => $validated['programa_id'],
            'descripcion' => trim($validated['descripcion']),
            'activo' => true,
        ]);

        return redirect()->route('lineas-investigacion')
            ->with('success', 'Línea de Investigación registrada con éxito.');
    }

    public function edit($id)
    {
        $item = LineaInvestigacion::findOrFail($id);
        $programas = $this->getProgramas();
        return view('lineas.edit', compact('item', 'programas'));
    }

    public function update(Request $request, $id, UnicidadNombreService $unicidadService)
    {
        $validated = $request->validate([
            'nombre_investigacion' => 'required|min:3|max:100',
            'area_de_investigacion' => 'required|min:3|max:100',
            'programa_id' => 'required|integer',
            'descripcion' => 'required|min:3|max:500',
        ], [
            'nombre_investigacion.required' => 'El nombre de la línea es obligatorio.',
            'nombre_investigacion.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre_investigacion.max' => 'El nombre no puede exceder 100 caracteres.',
            'area_de_investigacion.required' => 'El área de investigación es obligatoria.',
            'area_de_investigacion.min' => 'El área debe tener al menos 3 caracteres.',
            'area_de_investigacion.max' => 'El área no puede exceder 100 caracteres.',
            'programa_id.required' => 'Debe seleccionar un programa.',
            'programa_id.integer' => 'El programa seleccionado no es válido.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 3 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
        ]);

        $nombre = trim($validated['nombre_investigacion']);

        $item = LineaInvestigacion::findOrFail($id);

        if ($item->nombre_investigacion !== $nombre) {
            $disponible = $unicidadService->check(
                LineaInvestigacion::class,
                'nombre_investigacion',
                $nombre,
                $id,
            );
            if (!$disponible) {
                return back()->withErrors(['nombre_investigacion' => 'Este nombre ya está en uso.'])->withInput();
            }
        }

        LineaInvestigacion::guardar([
            'nombre_investigacion' => $nombre,
            'area_de_investigacion' => trim($validated['area_de_investigacion']),
            'programa_id' => $validated['programa_id'],
            'descripcion' => trim($validated['descripcion']),
        ], $id);

        return redirect()->route('lineas-investigacion')
            ->with('success', 'Línea de Investigación actualizada con éxito.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $item = LineaInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $mensaje = $item->activo
            ? 'Línea habilitada correctamente.'
            : 'Línea deshabilitada correctamente.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'nuevo_estado' => $item->activo,
            ]);
        }

        return redirect()->route('lineas-investigacion')
            ->with('success', $mensaje);
    }

    public function destroy(Request $request, $id)
    {
        $item = LineaInvestigacion::findOrFail($id);

        try {
            // Limpiar columna legacy lin_codigo
            try {
                DB::connection(config('dual_database.repositorio_connection', 'pgsql'))
                    ->table('proyectos')
                    ->where('lin_codigo', $id)
                    ->update(['lin_codigo' => null]);
            } catch (\Exception $e) {
            }

            // Limpiar columna FK linea_investigacion_id
            try {
                DB::connection(config('dual_database.repositorio_connection', 'pgsql'))
                    ->table('proyectos')
                    ->where('linea_investigacion_id', $id)
                    ->update(['linea_investigacion_id' => null]);
            } catch (\Exception $e) {
            }

            $item->borrar();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Línea de Investigación eliminada correctamente.']);
            }

            return redirect()->route('lineas-investigacion')
                ->with('success', 'Línea de Investigación eliminada correctamente.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se pudo eliminar: ' . $e->getMessage()]);
            }

            return redirect()->route('lineas-investigacion')
                ->with('error', 'No se pudo eliminar la línea porque está siendo utilizada por uno o más proyectos.');
        }
    }

    public function checkNombre(Request $request, UnicidadNombreService $unicidadService): JsonResponse
    {
        $nombre = trim($request->get('nombre', ''));
        $ignoreId = $request->integer('ignore_id', 0) ?: null;

        if ($nombre === '' || strlen($nombre) < 3) {
            return response()->json(['disponible' => false, 'error' => 'too_short']);
        }

        $disponible = $unicidadService->check(
            LineaInvestigacion::class,
            'nombre_investigacion',
            $nombre,
            $ignoreId,
        );

        return response()->json(['disponible' => $disponible]);
    }

    private function getProgramas()
    {
        $conn = DualDatabase::academicConnection();
        return DB::connection($conn)
            ->table('programa')
            ->select('pro_codigo as id', 'pro_nombre as nombre', 'pro_siglas as siglas')
            ->orderBy('pro_nombre')
            ->get();
    }
}
