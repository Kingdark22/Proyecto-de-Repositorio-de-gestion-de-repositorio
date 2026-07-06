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
        
        $writer = new SpreadsheetMlWriter();
        $writer->setTitle('Vinculaciones')
            ->setHeaderStyle('#8b0000', '#FFFFFF')
            ->setTitleStyle('#8b0000', '#FFFFFF')
            ->setAltRowStyle('#f5ebeb');

        $headers = [
            'SEDE', 'PROGRAMA NACIONAL DE FORMACIÓN', 'TRAYECTO', 'SEMESTRE', 
            'TÍTULO DE PROYECTO', 'RESUMEN O PRESENTACIÓN (NO MAS DE 150 PALABRAS)', 
            'LÍNEA DE INVESTIGACIÓN', 'DOCENTE DE PROYECTO', 'TUTOR ACADÉMICO', 
            'REPRESENTANTE INSTITUCIONAL', 
            'INTEGRANTE Nº 1; CÉDULA DE IDENTIDAD', 'INTEGRANTE Nº 2; CÉDULA DE IDENTIDAD', 
            'INTEGRANTE Nº 3; CÉDULA DE IDENTIDAD', 'INTEGRANTE Nº 4; CÉDULA DE IDENTIDAD', 
            'INTEGRANTE Nº 5; CÉDULA DE IDENTIDAD', 'INTEGRANTE Nº 6; CÉDULA DE IDENTIDAD', 
            'LOCALIDAD GEOGRÁFICA DONDE SE DESARROLLÓ EL PROYECTO (PARROQUIA, URBANIZACIÓN, BARRIO, ENTRE OTROS)', 
            'COMUNIDAD BENEFICIADA', 'RESULTADO de la SOCIALIZACIÓN'
        ];

        $widths = [
            40, 60, 20, 20, 60, 150, 60, 60, 60, 60, 
            60, 60, 60, 60, 60, 60, 100, 60, 60
        ];

        $totalCols = count($headers);
        
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

        $writer->addMergedTitleRow(
            'UPTP JUAN DE JESUS MONTILLA – VINCULACIONES — ' . strtoupper($tituloReporte),
            $totalCols,
            '#8b0000'
        );

        $writer->addRow($headers, isHeader: true, height: 40, widths: $widths);

        $idx = 0;
        foreach ($vinculaciones as $v) {
            $idx++;
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
            
            // Sede completa
            $sedeNombre = '';
            if ($ctx && !empty($ctx['sed_siglas'])) {
                try {
                    $academicConn = $equipoSvc->academicConnection();
                    $sedeConn = $academicConn === 'intranet' ? 'simulacion' : $academicConn;
                    $sedeNombre = DB::connection($sedeConn)->table('sede')
                        ->where('sed_siglas', $ctx['sed_siglas'])
                        ->value('sed_nombre') ?? $ctx['sed_siglas'];
                } catch (\Throwable $e) {
                    $sedeNombre = $ctx['sed_siglas'];
                }
            }

            // Docente desde SUD Intranet
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

            // Tutor y Representante desde Involucrados
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

            // Integrantes (Exacto: NOMBRE APELLIDO C.I V-XXXX)
            $integrantes = $equipoSvc->integrantes($equipoRef);
            $miembros = [];
            for ($i = 0; $i < 6; $i++) {
                $m = $integrantes->get($i);
                if ($m) {
                    $nombreCompleto = strtoupper(trim($m->nombre . ' ' . $m->apellido));
                    $cedula = $m->cedula ?? '';
                    $miembros[] = $nombreCompleto . ' C.I ' . $cedula;
                } else {
                    $miembros[] = '';
                }
            }

            // Localidad Geográfica
            $loc = '';
            if ($p->comunidad && $p->comunidad->direccion) {
                $dir = $p->comunidad->direccion;
                $loc = trim(sprintf('%s, %s, %s', 
                    $dir->dir_calle ?? '', 
                    $dir->municipio->mun_nombre ?? '', 
                    $dir->municipio->estado->est_nombre ?? ''
                ));
            }

            $writer->addRow([
                strtoupper($sedeNombre),
                strtoupper($ctx['pro_siglas'] ?? 'PNF'),
                $ctx['tra_codigo'] ?? '',
                $ctx['sem_codigo'] ?? '',
                $v->titulo,
                $p->pry_resumen ?? '',
                $p->linea_investigacion?->nombre ?? '',
                $docente,
                $tutor,
                $representante,
                ...$miembros,
                $loc,
                $p->comunidad?->nombre ?? '',
                $v->estado_socializacion ?? 'APROBADO',
            ], wrap: true);
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
