<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Vinculacion;
use App\Services\IntranetEquipoSeccionService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class VinculacionReporteController extends Controller
{
    public function reportePdf(Request $request)
    {
        $filtroTitulo = trim($request->get('filtro_titulo', ''));
        $filtroLapso = $request->get('filtro_lapso', '');
        $proyectoIds = $request->get('proyectos', []);

        $query = Vinculacion::with([
            'proyecto.linea_investigacion',
            'proyecto.metodologia',
            'proyecto.tipo_investigacion',
            'proyecto.tipo_publicacion',
            'proyecto.objetivo_investigacion',
            'proyecto.comunidad',
            'comunidad',
            'tituloVinculacion',
        ]);

        if ($filtroTitulo !== '') {
            $term = '%' . $filtroTitulo . '%';
            $query->whereHas('tituloVinculacion', fn($q) => $q->where('tiv_titulo', 'ILIKE', $term));
        }

        if ($filtroLapso !== '') {
            $ids = $this->proyectosEnLapso((int) $filtroLapso);
            if (!empty($ids)) {
                $query->whereIn('proyecto_id', $ids);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (!empty($proyectoIds)) {
            $query->whereIn('proyecto_id', $proyectoIds);
        }

        $vinculaciones = $query->get()->sortBy('titulo');

        $proyectos = $vinculaciones->pluck('proyecto')->filter();
        Proyecto::precargarTitulos($proyectos);

        $equipoSeccion = app(IntranetEquipoSeccionService::class);
        foreach ($vinculaciones as $v) {
            $p = $v->proyecto;
            if ($p && $p->equipo_ref) {
                $v->integrantes = $equipoSeccion->integrantes($p->equipo_ref);
                $partes = $equipoSeccion->parsearClave($p->equipo_ref);
                $v->lapso = $partes ? ($partes['lap_codigo'] ?? null) : null;
            } else {
                $v->integrantes = collect();
                $v->lapso = null;
            }
        }

        $lapsos = collect($vinculaciones)->pluck('lapso')->unique()->filter()->sort();
        $lapsosNombres = $lapsos->mapWithKeys(function ($lapCodigo) {
            $lapso = \App\Models\LapsoAcademico::find((int) $lapCodigo);
            return [$lapCodigo => $lapso?->nombre ?? "Lapso #{$lapCodigo}"];
        })->all();

        $titulo = $filtroTitulo ?: 'Todos los proyectos vinculados';

        if ($filtroLapso !== '') {
            $lapso = \App\Models\LapsoAcademico::find((int) $filtroLapso);
            $titulo = 'Vinculaciones - Lapso ' . ($lapso?->nombre ?? $filtroLapso);
        }

        $pdf = Pdf::loadView('pdf.vinculacion-reporte', [
            'titulo' => $titulo,
            'vinculaciones' => $vinculaciones,
            'lapsosNombres' => $lapsosNombres ?? [],
            'fecha' => now()->format('d/m/Y'),
        ]);

        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $titulo);
        return $pdf->download("vinculacion_{$sanitized}.pdf");
    }

    protected function proyectosEnLapso(int $lapCodigo): array
    {
        $equipoSeccion = app(IntranetEquipoSeccionService::class);
        $proyectos = Proyecto::where('estado_logico', true)->get(['id', 'equipo_ref']);
        $ids = [];

        foreach ($proyectos as $p) {
            if (!$p->equipo_ref) continue;
            $partes = $equipoSeccion->parsearClave($p->equipo_ref);
            if ($partes && ($partes['lap_codigo'] ?? null) === $lapCodigo) {
                $ids[] = $p->id;
            }
        }

        return $ids;
    }
}
