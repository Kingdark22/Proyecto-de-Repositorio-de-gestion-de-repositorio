<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Vinculacion;
use App\Services\IntranetEquipoSeccionService;
use App\Services\SpreadsheetMlWriter;
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

    public function exportarExcel(Request $request)
    {
        $filtroTitulo = trim($request->get('filtro_titulo', ''));
        $filtroLapso = $request->get('filtro_lapso', '');
        $proyectoIds = $request->get('proyectos', []);

        $query = Vinculacion::with([
            'proyecto',
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
                $integrantes = $equipoSeccion->integrantes($p->equipo_ref);
                $v->integrantesLista = $integrantes->pluck('nombre_completo')->implode(', ');
                $partes = $equipoSeccion->parsearClave($p->equipo_ref);
                $v->lapso = $partes ? ($partes['lap_codigo'] ?? null) : null;
            } else {
                $v->integrantesLista = '';
                $v->lapso = null;
            }
        }

        $writer = new SpreadsheetMlWriter();
        $writer->setTitle('Vinculaciones')
            ->setHeaderStyle('#8b0000', '#FFFFFF')
            ->setTitleStyle('#8b0000', '#FFFFFF')
            ->setAltRowStyle('#f5ebeb');

        $headers = ['N°', 'Título Vinculación', 'Proyecto', 'Comunidad', 'Equipo / Sección', 'Lapso'];
        $widths  = [6, 40, 60, 40, 25, 15];

        $totalCols = count($headers);

        $tituloReporte = $filtroTitulo ?: ($filtroLapso ? 'Vinculaciones - Lapso' : 'Todas las vinculaciones');
        $writer->addMergedTitleRow(
            'UPTP JUAN DE JESUS MONTILLA – VINCULACIONES — ' . strtoupper($tituloReporte),
            $totalCols,
            '#8b0000'
        );

        $writer->addRow($headers, isHeader: true, height: 35, widths: $widths);

        $idx = 0;
        foreach ($vinculaciones as $v) {
            $idx++;
            $lapsoNombre = '';
            if ($v->lapso) {
                $lapso = \App\Models\LapsoAcademico::find((int) $v->lapso);
                $lapsoNombre = $lapso?->nombre ?? '';
            }
            $writer->addRow([
                $idx,
                $v->titulo,
                $v->proyecto?->titulo ?? '',
                $v->comunidad?->nombre ?? '',
                $v->proyecto?->equipo_ref ?? '',
                $lapsoNombre,
            ], wrap: true);
        }

        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $tituloReporte);
        return $writer->download("vinculacion_{$sanitized}.xls");
    }

    protected function proyectosEnLapso(int $lapCodigo): array
    {
        $equipoSeccion = app(IntranetEquipoSeccionService::class);
        $proyectos = Proyecto::where('estado_logico', true)->select('id', 'equipo_ref')->get();
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
