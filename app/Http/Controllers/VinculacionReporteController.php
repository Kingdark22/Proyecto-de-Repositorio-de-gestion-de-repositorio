<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Vinculacion;
use App\Services\IntranetEquipoSeccionService;
use App\Services\ProyectoGestionService;
use App\Services\SpreadsheetMlWriter;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // Agrupar vinculaciones por título de vinculación
        $gruposPorTitulo = $vinculaciones->groupBy(function ($v) {
            return $v->tituloVinculacion?->titulo ?? 'Sin título';
        });

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
            'gruposPorTitulo' => $gruposPorTitulo,
            'lapsosNombres' => $lapsosNombres ?? [],
            'fecha' => now()->format('d/m/Y'),
            'esFiltroEspecifico' => ($filtroTitulo !== ''),
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
            'proyecto.linea_investigacion',
            'proyecto.comunidad.direccion.municipio.estado',
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

        $equipoSvc = app(IntranetEquipoSeccionService::class);
        $gestionSvc = app(ProyectoGestionService::class);

        // ── Primera pasada: resolver datos y encontrar max integrantes ──
        $rows = [];
        $maxIntegrantes = 0;

        foreach ($vinculaciones as $v) {
            $p = $v->proyecto;
            if (!$p) continue;

            $equipoRef = $p->equipo_ref ?? '';
            $partes = $equipoSvc->parsearClave($equipoRef);
            $ctx = [];
            if ($partes) {
                $ctx = $equipoSvc->etiquetasContexto(
                    $partes['lap_codigo'],
                    $partes['sec_codigo'],
                    $p->linea_investigacion_id ?? null
                );
            }

            $sedeNombre = '';
            $sedSiglas = $ctx['sed_siglas'] ?? '';

            // Fallback: extraer sede directamente del equipo_ref (ej: PNFI-ACA11-131-1 → ACA)
            if ($sedSiglas === '' && preg_match('/^[A-Z]+-([A-Z]{2,4})\d+-\d+/', strtoupper($equipoRef), $m)) {
                $sedSiglas = $m[1];
            }

            if ($sedSiglas !== '') {
                try {
                    $academicConn = $equipoSvc->academicConnection();
                    $sedeConn = $academicConn === 'intranet' ? 'simulacion' : $academicConn;
                    $sedeNombre = DB::connection($sedeConn)->table('sede')
                        ->where('sed_siglas', $sedSiglas)
                        ->value('sed_nombre') ?? $sedSiglas;
                } catch (\Throwable $e) {
                    $sedeNombre = $sedSiglas;
                }
            }

            // Programa: nombre completo
            $proNombre = $ctx['pro_nombre'] ?? '';
            $proSiglas = $ctx['pro_siglas'] ?? 'PNF';
            $programaInfo = $proNombre !== '' ? $proNombre : $proSiglas;

            $docente = '';
            if ($partes) {
                try {
                    $conn = DB::connection($equipoSvc->academicConnection());
                    $sud = $conn->table('seccion_unidad_docente')
                        ->where('sud_cod_seccion', $partes['sec_codigo'])
                        ->first();
                    if ($sud) {
                        $prof = $conn->table('persona')
                            ->where('per_cedula', $sud->sud_ced_docente)
                            ->first();
                        $docente = $prof ? strtoupper($prof->per_nombres . ' ' . $prof->per_apellidos) : '';
                    }
                } catch (\Throwable $e) {
                    Log::error('Error obteniendo docente SUD: ' . $e->getMessage());
                }
            }

            $tutor = ''; $representante = '';
            try {
                $involucrados = $gestionSvc->involucradosDelProyecto($p->pry_codigo);
                foreach ($involucrados as $inv) {
                    foreach ($inv['roles'] as $rol) {
                        $rolNombre = strtoupper($rol['nombre']);
                        if (str_contains($rolNombre, 'TUTOR')) $tutor = strtoupper($inv['nombre'] . ' ' . $inv['apellido']);
                        if (str_contains($rolNombre, 'REPRESENTANTE')) $representante = strtoupper($inv['nombre'] . ' ' . $inv['apellido']);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Error obteniendo involucrados: ' . $e->getMessage());
            }

            $integrantes = $equipoSvc->integrantes($equipoRef);
            $totalInt = $integrantes->count();
            if ($totalInt > $maxIntegrantes) {
                $maxIntegrantes = $totalInt;
            }

            $miembros = [];
            foreach ($integrantes as $m) {
                $miembros[] = [
                    'nombre' => strtoupper(trim($m->nombre . ' ' . $m->apellido)),
                    'cedula' => $m->cedula ?? '',
                ];
            }

            $loc = '';
            if ($p->comunidad && $p->comunidad->direccion) {
                $dir = $p->comunidad->direccion;
                $loc = trim(sprintf('%s, %s, %s',
                    $dir->dir_calle ?? '',
                    $dir->municipio->mun_nombre ?? '',
                    $dir->municipio->estado->est_nombre ?? ''
                ));
            }

            $rows[] = [
                'sede' => strtoupper($sedeNombre),
                'programa' => strtoupper($programaInfo),
                'trayecto' => $ctx['tra_codigo'] ?? '',
                'semestre' => $ctx['sem_codigo'] ?? '',
                'titulo_proyecto' => $v->titulo,
                'resumen' => $p->pry_resumen ?? '',
                'linea' => $p->linea_investigacion?->nombre ?? '',
                'docente' => $docente,
                'tutor' => $tutor,
                'representante' => $representante,
                'integrantes' => $miembros,
                'localidad' => $loc,
                'comunidad' => $p->comunidad?->nombre ?? '',
                'socializacion' => $v->estado_socializacion ?? 'APROBADO',
            ];
        }

        if ($maxIntegrantes < 1) $maxIntegrantes = 1;

        // ── Construir headers dinámicos ──
        $headers = [
            'SEDE',
            'PROGRAMA NACIONAL DE FORMACIÓN',
            'TRAYECTO',
            'SEMESTRE',
            'TÍTULO DE PROYECTO',
            'RESUMEN O PRESENTACIÓN (NO MAS DE 150 PALABRAS)',
            'LÍNEA DE INVESTIGACIÓN',
            'DOCENTE DE PROYECTO',
            'TUTOR ACADÉMICO',
            'REPRESENTANTE INSTITUCIONAL',
        ];

        for ($i = 1; $i <= $maxIntegrantes; $i++) {
            $headers[] = "INTEGRANTE N° $i; NOMBRE Y APELLIDO";
            $headers[] = "INTEGRANTE N° $i; CÉDULA DE IDENTIDAD";
        }

        $headers[] = 'LOCALIDAD GEOGRÁFICA DONDE SE DESARROLLÓ EL PROYECTO (PARROQUIA, URBANIZACIÓN, BARRIO, ENTRE OTROS)';
        $headers[] = 'COMUNIDAD BENEFICIADA';
        $headers[] = 'RESULTADO DE LA SOCIALIZACIÓN';

        $totalCols = count($headers);

        $widths = [];
        foreach ($headers as $h) {
            if (str_contains($h, 'RESUMEN')) $widths[] = 150;
            elseif (str_contains($h, 'LOCALIDAD')) $widths[] = 100;
            elseif (str_contains($h, 'PROGRAMA')) $widths[] = 80;
            elseif (str_contains($h, 'INTEGRANTE N°')) $widths[] = 60;
            elseif (str_contains($h, 'CÉDULA')) $widths[] = 40;
            else $widths[] = 50;
        }

        if ($filtroTitulo !== '') {
            $tituloReporte = 'Vinculaciones - ' . $filtroTitulo;
            $nombreArchivo = "vinculacion_titulo_" . strtolower(str_replace(' ', '_', $filtroTitulo));
        } elseif ($filtroLapso !== '') {
            $lapsoObj = \App\Models\LapsoAcademico::find((int) $filtroLapso);
            $nombreLapso = $lapsoObj?->nombre ?? $filtroLapso;
            $tituloReporte = 'Vinculaciones - ' . $nombreLapso;
            $nombreArchivo = "vinculacion_lapso_" . $filtroLapso;
        } else {
            $tituloReporte = 'Todas las Vinculaciones';
            $nombreArchivo = 'vinculacion_general';
        }

        $writer = new SpreadsheetMlWriter();
        $writer->setTitle('Vinculaciones')
            ->setHeaderStyle('#8b0000', '#FFFFFF')
            ->setTitleStyle('#8b0000', '#FFFFFF')
            ->setAltRowStyle('#f5ebeb');

        $writer->addMergedTitleRow(
            'UPTP JUAN DE JESUS MONTILLA – VINCULACIONES — ' . strtoupper($tituloReporte),
            $totalCols,
            '#8b0000'
        );

        $writer->addRow($headers, isHeader: true, height: 40, widths: $widths);

        foreach ($rows as $r) {
            $data = [
                $r['sede'],
                $r['programa'],
                $r['trayecto'],
                $r['semestre'],
                $r['titulo_proyecto'],
                $r['resumen'],
                $r['linea'],
                $r['docente'],
                $r['tutor'],
                $r['representante'],
            ];

            for ($i = 0; $i < $maxIntegrantes; $i++) {
                $m = $r['integrantes'][$i] ?? null;
                $data[] = $m ? $m['nombre'] : '';
                $data[] = $m ? $m['cedula'] : '';
            }

            $data[] = $r['localidad'];
            $data[] = $r['comunidad'];
            $data[] = $r['socializacion'];

            $writer->addRow($data, wrap: true);
        }

        return $writer->download($nombreArchivo . '.xls');
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
