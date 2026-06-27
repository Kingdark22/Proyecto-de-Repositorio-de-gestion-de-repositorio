<?php

namespace App\Http\Controllers;

use App\Models\TipoInvestigacion;
use App\Services\UnicidadNombreService;
use Illuminate\Http\Request;

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
            'nombre' => 'required|min:3|max:255',
            'descripcion' => 'required|min:3|max:500',
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
            'nombre' => 'required|min:3|max:255',
            'descripcion' => 'required|min:3|max:500',
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

    public function toggleStatus($id)
    {
        $item = TipoInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $mensaje = $item->estado_logico
            ? 'Tipo habilitado correctamente.'
            : 'Tipo deshabilitado correctamente.';

        return redirect()->route('tipos-investigacion')
            ->with('success', $mensaje);
    }

    public function destroy($id)
    {
        $item = TipoInvestigacion::findOrFail($id);
        $item->borrar();

        return redirect()->route('tipos-investigacion')
            ->with('success', 'Tipo de Investigación eliminado correctamente.');
    }
}
