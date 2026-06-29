<?php

namespace App\Http\Controllers;

use App\Models\MetodologiaInvestigacion;
use App\Services\UnicidadNombreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetodologiaInvestigacionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $items = MetodologiaInvestigacion::where('nombre', 'ILIKE', '%' . $search . '%')
            ->latest()
            ->paginate(10);

        return view('metodologia_investigacion.index', compact('items', 'search'));
    }

    public function create()
    {
        return view('metodologia_investigacion.create');
    }

    public function store(Request $request, UnicidadNombreService $unicidadService)
    {
        $validated = $request->validate([
            'nombre' => 'required|min:3|max:100',
            'descripcion' => 'required|min:3|max:500',
        ], [
            'nombre.required' => 'El nombre de la metodología es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 3 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
        ]);

        $nombre = trim($validated['nombre']);

        $disponible = $unicidadService->check(
            MetodologiaInvestigacion::class,
            'nombre',
            $nombre,
            null,
        );

        if (!$disponible) {
            return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
        }

        MetodologiaInvestigacion::guardar([
            'nombre' => $nombre,
            'descripcion' => trim($validated['descripcion']),
            'estado_logico' => true,
        ]);

        return redirect()->route('metodologia-investigacion')
            ->with('success', 'Metodología registrada con éxito.');
    }

    public function edit($id)
    {
        $item = MetodologiaInvestigacion::findOrFail($id);
        return view('metodologia_investigacion.edit', compact('item'));
    }

    public function update(Request $request, $id, UnicidadNombreService $unicidadService)
    {
        $validated = $request->validate([
            'nombre' => 'required|min:3|max:100',
            'descripcion' => 'required|min:3|max:500',
        ], [
            'nombre.required' => 'El nombre de la metodología es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 3 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
        ]);

        $nombre = trim($validated['nombre']);

        $item = MetodologiaInvestigacion::findOrFail($id);

        if ($item->nombre !== $nombre) {
            $disponible = $unicidadService->check(
                MetodologiaInvestigacion::class,
                'nombre',
                $nombre,
                $id,
            );
            if (!$disponible) {
                return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
            }
        }

        MetodologiaInvestigacion::guardar([
            'nombre' => $nombre,
            'descripcion' => trim($validated['descripcion']),
        ], $id);

        return redirect()->route('metodologia-investigacion')
            ->with('success', 'Metodología actualizada con éxito.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $item = MetodologiaInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $mensaje = $item->estado_logico
            ? 'Metodología habilitada correctamente.'
            : 'Metodología deshabilitada correctamente.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'nuevo_estado' => $item->estado_logico,
            ]);
        }

        return redirect()->route('metodologia-investigacion')
            ->with('success', $mensaje);
    }

    public function destroy(Request $request, $id)
    {
        $item = MetodologiaInvestigacion::findOrFail($id);

        try {
            try {
                DB::connection(config('dual_database.repositorio_connection', 'pgsql'))
                    ->table('proyectos')
                    ->where('mei_codigo', $id)
                    ->update(['mei_codigo' => null]);
            } catch (\Exception $e) {
            }

            try {
                DB::connection(config('dual_database.repositorio_connection', 'pgsql'))
                    ->table('proyectos')
                    ->where('metodologia_id', $id)
                    ->update(['metodologia_id' => null]);
            } catch (\Exception $e) {
            }

            $item->borrar();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Metodología eliminada correctamente.']);
            }

            return redirect()->route('metodologia-investigacion')
                ->with('success', 'Metodología eliminada correctamente.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se pudo eliminar: ' . $e->getMessage()]);
            }

            return redirect()->route('metodologia-investigacion')
                ->with('error', 'No se pudo eliminar la metodología porque está siendo utilizada por uno o más proyectos.');
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
            MetodologiaInvestigacion::class,
            'nombre',
            $nombre,
            $ignoreId,
        );

        return response()->json(['disponible' => $disponible]);
    }
}
