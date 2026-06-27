<?php

namespace App\Http\Controllers;

use App\Models\ObjetivoInvestigacion;
use App\Services\UnicidadNombreService;
use Illuminate\Http\Request;

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
            'nombre' => 'required|min:3|max:255',
            'descripcion' => 'required|min:3|max:500',
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
            'nombre' => 'required|min:3|max:255',
            'descripcion' => 'required|min:3|max:500',
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

    public function toggleStatus($id)
    {
        $item = ObjetivoInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $mensaje = $item->estado_logico
            ? 'Objetivo habilitado correctamente.'
            : 'Objetivo deshabilitado correctamente.';

        return redirect()->route('objetivos-investigacion')
            ->with('success', $mensaje);
    }

    public function destroy($id)
    {
        $item = ObjetivoInvestigacion::findOrFail($id);
        $item->borrar();

        return redirect()->route('objetivos-investigacion')
            ->with('success', 'Objetivo de Investigación eliminado correctamente.');
    }
}
