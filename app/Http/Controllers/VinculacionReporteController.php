<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Vinculacion;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class VinculacionReporteController extends Controller
{
    public function reportePdf(Request $request)
    {
        $filtroTitulo = trim($request->get('filtro_titulo', ''));
        $proyectoIds = $request->get('proyectos', []);

        $query = Vinculacion::with('proyecto', 'comunidad');

        if ($filtroTitulo !== '') {
            $term = '%' . $filtroTitulo . '%';
            $query->where('vin_titulo', 'ILIKE', $term);
        }

        if (!empty($proyectoIds)) {
            $query->whereIn('proyecto_id', $proyectoIds);
        }

        $vinculaciones = $query->orderBy('vin_titulo')->get();

        $titulo = $filtroTitulo ?: 'Todos los proyectos vinculados';

        $pdf = Pdf::loadView('pdf.vinculacion-reporte', [
            'titulo' => $titulo,
            'vinculaciones' => $vinculaciones,
            'fecha' => now()->format('d/m/Y'),
        ]);

        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $titulo);
        return $pdf->download("vinculacion_{$sanitized}.pdf");
    }
}
