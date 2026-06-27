<?php

namespace App\Http\Controllers;

use App\Helpers\DualDatabase;
use App\Models\LineaInvestigacion;
use App\Services\UnicidadNombreService;
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
            'nombre_investigacion' => 'required|min:3|max:255',
            'area_de_investigacion' => 'required|min:3|max:255',
            'programa_id' => 'required|integer',
            'descripcion' => 'required|min:3|max:500',
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
            'nombre_investigacion' => 'required|min:3|max:255',
            'area_de_investigacion' => 'required|min:3|max:255',
            'programa_id' => 'required|integer',
            'descripcion' => 'required|min:3|max:500',
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

    public function toggleStatus($id)
    {
        $item = LineaInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $mensaje = $item->activo
            ? 'Línea habilitada correctamente.'
            : 'Línea deshabilitada correctamente.';

        return redirect()->route('lineas-investigacion')
            ->with('success', $mensaje);
    }

    public function destroy($id)
    {
        $item = LineaInvestigacion::findOrFail($id);
        $item->borrar();

        return redirect()->route('lineas-investigacion')
            ->with('success', 'Línea de Investigación eliminada correctamente.');
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
