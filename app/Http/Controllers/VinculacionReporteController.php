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

        // ── Pre-cargar datos en lote (evita N+1) ──────────────────────────
        $equipoRefs = $vinculaciones->pluck('proyecto.equipo_ref')->filter()->unique()->values()->toArray();
        $proyectoIds = $vinculaciones->pluck('proyecto.pry_codigo')->filter()->unique()->values()->toArray();

        // Pre-cargar sedes
        $sedesMap = [];
        $sedSiglasSet = [];
        foreach ($equipoRefs as $ref) {
            $partes = $equipoSvc->parsearClave($ref);
            if ($partes) {
                $ctx = $equipoSvc->etiquetasContexto($partes['lap_codigo'], $partes['sec_codigo'], null);
                $siglas = $ctx['sed_siglas'] ?? '';
                if ($siglas === '' && preg_match('/^[A-Z]+-([A-Z]{2,4})\d+-\d+/', strtoupper($ref), $m)) {
                    $siglas = $m[1];
                }
                if ($siglas !== '') $sedSiglasSet[$siglas] = true;
            }
        }
        if (!empty($sedSiglasSet)) {
            try {
                $academicConn = $equipoSvc->academicConnection();
                $sedeConn = $academicConn === 'intranet' ? 'simulacion' : $academicConn;
                $sedRows = DB::connection($sedeConn)->table('sede')
                    ->whereIn('sed_siglas', array_keys($sedSiglasSet))
                    ->get(['sed_siglas', 'sed_nombre']);
                foreach ($sedRows as $s) {
                    $sedesMap[$s->sed_siglas] = strtoupper($s->sed_nombre);
                }
            } catch (\Throwable) {}
        }

        // Pre-cargar docentes de secciones en lote (evita N+1)
        $docentesMap = [];
        $secCodesSet = [];
        foreach ($equipoRefs as $ref) {
            $partes = $equipoSvc->parsearClave($ref);
            if ($partes && !isset($secCodesSet[$partes['sec_codigo']])) {
                $secCodesSet[$partes['sec_codigo']] = true;
            }
        }
        if (!empty($secCodesSet)) {
            try {
                $conn = DB::connection($equipoSvc->academicConnection());
                $sudRows = $conn->table('seccion_unidad_docente as sud')
                    ->join('persona as p', 'p.per_cedula', '=', 'sud.sud_ced_docente')
                    ->whereIn('sud.sud_cod_seccion', array_keys($secCodesSet))
                    ->select(['sud.sud_cod_seccion', 'p.per_nombres', 'p.per_apellidos'])
                    ->get();
                foreach ($sudRows as $r) {
                    $docentesMap[$r->sud_cod_seccion] = strtoupper(trim($r->per_nombres . ' ' . $r->per_apellidos));
                }
            } catch (\Throwable) {}
        }

        // Pre-cargar involucrados (tutor y representante) en lote
        $involucradosMap = [];
        if (!empty($proyectoIds)) {
            try {
                $conn = (string) config('dual_database.repositorio_connection', 'pgsql');
                $invRows = DB::connection($conn)
                    ->table('proyecto_involucrado as pi')
                    ->join('involucrados as i', 'i.inv_codigo', '=', 'pi.inv_codigo')
                    ->join('detalle_involucrados_rol as dir', 'dir.inv_codigo', '=', 'i.inv_codigo')
                    ->join('roles_involucrados as ri', 'ri.rin_codigo', '=', 'dir.rin_codigo')
                    ->whereIn('pi.pry_codigo', $proyectoIds)
                    ->where(function ($q) {
                        $q->whereRaw("LOWER(ri.rin_nombre) LIKE ?", ['%tutor%'])
                          ->orWhereRaw("LOWER(ri.rin_nombre) LIKE ?", ['%representante%']);
                    })
                    ->select([
                        'pi.pry_codigo as proyecto_id',
                        DB::raw("TRIM(i.inv_nombre) as nombre"),
                        DB::raw("TRIM(i.inv_apellido) as apellido"),
                        DB::raw("LOWER(ri.rin_nombre) as rol_lower"),
                    ])
                    ->distinct()
                    ->get();
                foreach ($invRows as $row) {
                    $pid = $row->proyecto_id;
                    if (!isset($involucradosMap[$pid])) {
                        $involucradosMap[$pid] = ['tutor' => '', 'representante' => ''];
                    }
                    $nombre = strtoupper(trim($row->nombre . ' ' . $row->apellido));
                    if (str_contains($row->rol_lower, 'tutor') && $involucradosMap[$pid]['tutor'] === '') {
                        $involucradosMap[$pid]['tutor'] = $nombre;
                    }
                    if (str_contains($row->rol_lower, 'representante') && $involucradosMap[$pid]['representante'] === '') {
                        $involucradosMap[$pid]['representante'] = $nombre;
                    }
                }
            } catch (\Throwable) {}
        }

        // Pre-cargar integrantes en lote
        $integrantesMap = [];
        foreach ($equipoRefs as $ref) {
            if (!isset($integrantesMap[$ref])) {
                try {
                    $integrantesMap[$ref] = $equipoSvc->integrantes($ref);
                } catch (\Throwable) {
                    $integrantesMap[$ref] = collect();
                }
            }
        }

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

            // Sede desde pre-carga
            $sedeNombre = '';
            $sedSiglas = $ctx['sed_siglas'] ?? '';
            if ($sedSiglas === '' && preg_match('/^[A-Z]+-([A-Z]{2,4})\d+-\d+/', strtoupper($equipoRef), $m)) {
                $sedSiglas = $m[1];
            }
            $sedeNombre = $sedesMap[$sedSiglas] ?? $sedSiglas;

            // Programa
            $proNombre = $ctx['pro_nombre'] ?? '';
            $proSiglas = $ctx['pro_siglas'] ?? 'PNF';
            $programaInfo = $proNombre !== '' ? $proNombre : $proSiglas;

            // Docente desde pre-carga
            $docente = $partes ? ($docentesMap[$partes['sec_codigo']] ?? '') : '';

            // Tutor y representante desde pre-carga
            $tutor = $involucradosMap[$p->pry_codigo]['tutor'] ?? '';
            $representante = $involucradosMap[$p->pry_codigo]['representante'] ?? '';

            // Integrantes desde pre-carga
            $integrantes = $integrantesMap[$equipoRef] ?? collect();
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
                'socializacion' => 'APROBADO',
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
        // Usar LIKE indexado sobre pry_direccion_logica para patrón EQSEC:{lapso}:*
        $likePattern = 'EQSEC:' . $lapCodigo . ':%';

        return Proyecto::where('pry_estado', 'Aprobado')
            ->where(function ($q) use ($likePattern, $lapCodigo) {
                $q->where('pry_direccion_logica', 'LIKE', $likePattern)
                  ->orWhereIn('pry_direccion_logica', function ($sub) use ($lapCodigo) {
                      $sub->select('grp_identificador')
                          ->from('grupo_proyecto_modulo')
                          ->whereRaw("CAST(grp_contexto AS jsonb)->>'lap_codigo' = ?", [(string) $lapCodigo])
                          ->where('estado_logico', true)
                          ->whereNotNull('grp_identificador');
                  });
            })
            ->pluck('id')
            ->toArray();
    }
}
