<?php

namespace App\Http\Controllers;

use App\Models\TipoInvestigacion;
use App\Services\UnicidadNombreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoInvestigacionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $items = TipoInvestigacion::where('nombre', 'ILIKE', '%' . $search . '%')
            ->latest()
            ->paginate(10);

        return view('tipo_investigacion.index', compact('items', 'search'));
    }

    public function create()
    {
        return view('tipo_investigacion.create');
    }

    public function store(Request $request, UnicidadNombreService $unicidadService)
    {
        $validated = $request->validate([
            'nombre' => 'required|min:3|max:100',
            'descripcion' => 'required|min:3|max:500',
        ], [
            'nombre.required' => 'El nombre del tipo de investigación es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 3 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
        ]);

        $nombre = trim($validated['nombre']);

        $disponible = $unicidadService->check(
            TipoInvestigacion::class,
            'nombre',
            $nombre,
            null,
        );

        if (!$disponible) {
            return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
        }

        TipoInvestigacion::guardar([
            'nombre' => $nombre,
            'descripcion' => trim($validated['descripcion']),
            'estado_logico' => true,
        ]);

        return redirect()->route('tipos-investigacion')
            ->with('success', 'Tipo de Investigación registrado con éxito.');
    }

    public function edit($id)
    {
        $item = TipoInvestigacion::findOrFail($id);
        return view('tipo_investigacion.edit', compact('item'));
    }

    public function update(Request $request, $id, UnicidadNombreService $unicidadService)
    {
        $validated = $request->validate([
            'nombre' => 'required|min:3|max:100',
            'descripcion' => 'required|min:3|max:500',
        ], [
            'nombre.required' => 'El nombre del tipo de investigación es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 3 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
        ]);

        $nombre = trim($validated['nombre']);

        $item = TipoInvestigacion::findOrFail($id);

        if ($item->nombre !== $nombre) {
            $disponible = $unicidadService->check(
                TipoInvestigacion::class,
                'nombre',
                $nombre,
                $id,
            );
            if (!$disponible) {
                return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
            }
        }

        TipoInvestigacion::guardar([
            'nombre' => $nombre,
            'descripcion' => trim($validated['descripcion']),
        ], $id);

        return redirect()->route('tipos-investigacion')
            ->with('success', 'Tipo de Investigación actualizado con éxito.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $item = TipoInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $mensaje = $item->estado_logico
            ? 'Tipo habilitado correctamente.'
            : 'Tipo deshabilitado correctamente.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'nuevo_estado' => $item->estado_logico,
            ]);
        }

        return redirect()->route('tipos-investigacion')
            ->with('success', $mensaje);
    }

    public function destroy(Request $request, $id)
    {
        $item = TipoInvestigacion::findOrFail($id);

        try {
            try {
                DB::connection(config('dual_database.repositorio_connection', 'pgsql'))
                    ->table('proyectos')
                    ->where('tin_codigo', $id)
                    ->update(['tin_codigo' => null]);
            } catch (\Exception $e) {
            }

            try {
                DB::connection(config('dual_database.repositorio_connection', 'pgsql'))
                    ->table('proyectos')
                    ->where('tipo_investigacion_id', $id)
                    ->update(['tipo_investigacion_id' => null]);
            } catch (\Exception $e) {
            }

            $item->borrar();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Tipo de Investigación eliminado correctamente.']);
            }

            return redirect()->route('tipos-investigacion')
                ->with('success', 'Tipo de Investigación eliminado correctamente.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se pudo eliminar: ' . $e->getMessage()]);
            }

            return redirect()->route('tipos-investigacion')
                ->with('error', 'No se pudo eliminar el tipo porque está siendo utilizado por uno o más proyectos.');
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
            TipoInvestigacion::class,
            'nombre',
            $nombre,
            $ignoreId,
        );

        return response()->json(['disponible' => $disponible]);
    }
}
