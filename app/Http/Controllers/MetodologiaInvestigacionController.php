<?php

namespace App\Http\Controllers;

use App\Models\MetodologiaInvestigacion;
use App\Services\UnicidadNombreService;
use Illuminate\Http\Request;

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
            'nombre' => 'required|min:3|max:255',
            'descripcion' => 'required|min:3|max:500',
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
            'nombre' => 'required|min:3|max:255',
            'descripcion' => 'required|min:3|max:500',
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

    public function toggleStatus($id)
    {
        $item = MetodologiaInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $mensaje = $item->estado_logico
            ? 'Metodología habilitada correctamente.'
            : 'Metodología deshabilitada correctamente.';

        return redirect()->route('metodologia-investigacion')
            ->with('success', $mensaje);
    }

    public function destroy($id)
    {
        $item = MetodologiaInvestigacion::findOrFail($id);
        $item->borrar();

        return redirect()->route('metodologia-investigacion')
            ->with('success', 'Metodología eliminada correctamente.');
    }
}
