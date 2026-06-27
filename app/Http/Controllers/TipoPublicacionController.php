<?php

namespace App\Http\Controllers;

use App\Models\TipoPublicacion;
use App\Services\UnicidadNombreService;
use Illuminate\Http\Request;

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
            'nombre' => 'required|min:3|max:255',
            'mencion_honorifica' => 'boolean',
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
            'nombre' => 'required|min:3|max:255',
            'mencion_honorifica' => 'boolean',
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

    public function toggleStatus($id)
    {
        $item = TipoPublicacion::findOrFail($id);
        $item->alternarEstado();

        $mensaje = $item->estado_logico
            ? 'Tipo habilitado correctamente.'
            : 'Tipo deshabilitado correctamente.';

        return redirect()->route('tipos-publicacion')
            ->with('success', $mensaje);
    }

    public function destroy($id)
    {
        $item = TipoPublicacion::findOrFail($id);
        $item->borrar();

        return redirect()->route('tipos-publicacion')
            ->with('success', 'Tipo de Publicación eliminado correctamente.');
    }
}
