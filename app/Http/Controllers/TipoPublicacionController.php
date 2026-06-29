<?php

namespace App\Http\Controllers;

use App\Models\TipoPublicacion;
use App\Services\UnicidadNombreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoPublicacionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $items = TipoPublicacion::where('nombre', 'ILIKE', '%' . $search . '%')
            ->latest()
            ->paginate(10);

        if ($request->ajax()) {
            return view('tipo_publicacion.index', compact('items'))->render();
        }

        return view('tipo_publicacion.index', compact('items', 'search'));
    }

    public function create()
    {
        return view('tipo_publicacion.create');
    }

    public function store(Request $request, UnicidadNombreService $unicidadService)
    {
        $validated = $request->validate([
            'nombre' => 'required|min:3|max:100',
            'mencion_honorifica' => 'boolean',
        ], [
            'nombre.required' => 'El nombre del tipo de publicación es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
        ]);

        $nombre = trim($validated['nombre']);

        $disponible = $unicidadService->check(
            TipoPublicacion::class,
            'nombre',
            $nombre,
            null,
        );

        if (!$disponible) {
            return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
        }

        TipoPublicacion::guardar([
            'nombre' => $nombre,
            'mencion_honorifica' => $validated['mencion_honorifica'] ?? false,
            'estado_logico' => true,
        ]);

        return redirect()->route('tipos-publicacion')
            ->with('success', 'Tipo de Publicación registrado con éxito.');
    }

    public function edit($id)
    {
        $item = TipoPublicacion::findOrFail($id);
        return view('tipo_publicacion.edit', compact('item'));
    }

    public function update(Request $request, $id, UnicidadNombreService $unicidadService)
    {
        $validated = $request->validate([
            'nombre' => 'required|min:3|max:100',
            'mencion_honorifica' => 'boolean',
        ], [
            'nombre.required' => 'El nombre del tipo de publicación es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
        ]);

        $nombre = trim($validated['nombre']);

        $item = TipoPublicacion::findOrFail($id);

        if ($item->nombre !== $nombre) {
            $disponible = $unicidadService->check(
                TipoPublicacion::class,
                'nombre',
                $nombre,
                $id,
            );
            if (!$disponible) {
                return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
            }
        }

        TipoPublicacion::guardar([
            'nombre' => $nombre,
            'mencion_honorifica' => $validated['mencion_honorifica'] ?? false,
        ], $id);

        return redirect()->route('tipos-publicacion')
            ->with('success', 'Tipo de Publicación actualizado con éxito.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $item = TipoPublicacion::findOrFail($id);
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

        return redirect()->route('tipos-publicacion')
            ->with('success', $mensaje);
    }

    public function destroy(Request $request, $id)
    {
        $item = TipoPublicacion::findOrFail($id);

        try {
            try {
                DB::connection(config('dual_database.repositorio_connection', 'pgsql'))
                    ->table('proyectos')
                    ->where('tpu_codigo', $id)
                    ->update(['tpu_codigo' => null]);
            } catch (\Exception $e) {
            }

            try {
                DB::connection(config('dual_database.repositorio_connection', 'pgsql'))
                    ->table('proyectos')
                    ->where('tipo_publicacion_id', $id)
                    ->update(['tipo_publicacion_id' => null]);
            } catch (\Exception $e) {
            }

            $item->borrar();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Tipo de Publicación eliminado correctamente.']);
            }

            return redirect()->route('tipos-publicacion')
                ->with('success', 'Tipo de Publicación eliminado correctamente.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se pudo eliminar: ' . $e->getMessage()]);
            }

            return redirect()->route('tipos-publicacion')
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
            TipoPublicacion::class,
            'nombre',
            $nombre,
            $ignoreId,
        );

        return response()->json(['disponible' => $disponible]);
    }
}
