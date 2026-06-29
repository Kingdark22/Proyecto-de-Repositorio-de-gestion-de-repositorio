<?php

namespace App\Http\Controllers;

use App\Models\ObjetivoInvestigacion;
use App\Services\UnicidadNombreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObjetivoInvestigacionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $items = ObjetivoInvestigacion::where('nombre', 'ILIKE', '%' . $search . '%')
            ->latest()
            ->paginate(10);

        return view('objetivo_investigacion.index', compact('items', 'search'));
    }

    public function create()
    {
        return view('objetivo_investigacion.create');
    }

    public function store(Request $request, UnicidadNombreService $unicidadService)
    {
        $validated = $request->validate([
            'nombre' => 'required|min:3|max:100',
            'descripcion' => 'required|min:3|max:500',
        ], [
            'nombre.required' => 'El nombre del objetivo de investigación es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 3 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
        ]);

        $nombre = trim($validated['nombre']);

        $disponible = $unicidadService->check(
            ObjetivoInvestigacion::class,
            'nombre',
            $nombre,
            null,
        );

        if (!$disponible) {
            return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
        }

        ObjetivoInvestigacion::guardar([
            'nombre' => $nombre,
            'descripcion' => trim($validated['descripcion']),
            'estado_logico' => true,
        ]);

        return redirect()->route('objetivos-investigacion')
            ->with('success', 'Objetivo de Investigación registrado con éxito.');
    }

    public function edit($id)
    {
        $item = ObjetivoInvestigacion::findOrFail($id);
        return view('objetivo_investigacion.edit', compact('item'));
    }

    public function update(Request $request, $id, UnicidadNombreService $unicidadService)
    {
        $validated = $request->validate([
            'nombre' => 'required|min:3|max:100',
            'descripcion' => 'required|min:3|max:500',
        ], [
            'nombre.required' => 'El nombre del objetivo de investigación es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 3 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
        ]);

        $nombre = trim($validated['nombre']);

        $item = ObjetivoInvestigacion::findOrFail($id);

        if ($item->nombre !== $nombre) {
            $disponible = $unicidadService->check(
                ObjetivoInvestigacion::class,
                'nombre',
                $nombre,
                $id,
            );
            if (!$disponible) {
                return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
            }
        }

        ObjetivoInvestigacion::guardar([
            'nombre' => $nombre,
            'descripcion' => trim($validated['descripcion']),
        ], $id);

        return redirect()->route('objetivos-investigacion')
            ->with('success', 'Objetivo de Investigación actualizado con éxito.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $item = ObjetivoInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $mensaje = $item->estado_logico
            ? 'Objetivo habilitado correctamente.'
            : 'Objetivo deshabilitado correctamente.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'nuevo_estado' => $item->estado_logico,
            ]);
        }

        return redirect()->route('objetivos-investigacion')
            ->with('success', $mensaje);
    }

    public function destroy(Request $request, $id)
    {
        $item = ObjetivoInvestigacion::findOrFail($id);

        try {
            DB::connection(config('dual_database.repositorio_connection', 'pgsql'))
                ->table('proyectos')
                ->where('objetivo_investigacion_id', $id)
                ->update(['objetivo_investigacion_id' => null]);

            $item->borrar();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Objetivo de Investigación eliminado correctamente.']);
            }

            return redirect()->route('objetivos-investigacion')
                ->with('success', 'Objetivo de Investigación eliminado correctamente.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se pudo eliminar: ' . $e->getMessage()]);
            }

            return redirect()->route('objetivos-investigacion')
                ->with('error', 'No se pudo eliminar el objetivo porque está siendo utilizado por uno o más proyectos.');
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
            ObjetivoInvestigacion::class,
            'nombre',
            $nombre,
            $ignoreId,
        );

        return response()->json(['disponible' => $disponible]);
    }
}
