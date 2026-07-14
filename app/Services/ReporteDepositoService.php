<?php

namespace App\Services;

use App\Models\Comunidad;
use App\Models\Proyecto;
use App\Repositories\GrupoProyectoRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de exportación del Depósito de Proyectos.
 *
 * Extrae la información de cada proyecto registrado en el repositorio,
 * complementándola con datos académicos (Sede, PNF, Trayecto, Sección, Lapso)
 * y con los integrantes del equipo e involucrados (Tutor Académico).
 *
 * El número de integrantes por equipo puede variar; el método genera una
 * estructura dinámica para acomodar esa variabilidad en el Excel.
 */
class ReporteDepositoService
{
    protected array $sedeCache = [];

    public function __construct(
        protected IntranetEquipoSeccionService $equipoSeccion,
        protected GrupoProyectoRepository $grupoRepo,
    ) {}

    /**
     * Construye un array de filas para el reporte Excel.
     * Cada fila es un array asociativo con claves normalizadas.
     *
     * @param  array<string, mixed>  $filtros  (search, comunidad, lapso_codigo, pro_siglas, programa, trayecto, seccion, linea, tipo_investigacion, metodologia)
     * @return array{maxIntegrantes: int, filas: list<array<string, mixed>>, lapsoMasActual: string, pnfPredominante: string}
     */
    public function construirFilasReporte(array $filtros = []): array
    {
        $proyectos = $this->obtenerProyectos($filtros);
        $lapsoFiltroCodigo = isset($filtros['lapso_codigo']) && $filtros['lapso_codigo'] !== '' ? (int) $filtros['lapso_codigo'] : null;
        $pnfFiltroSiglas   = isset($filtros['pro_siglas']) && $filtros['pro_siglas'] !== '' ? mb_strtoupper(trim($filtros['pro_siglas'])) : null;
        $programaFiltro    = isset($filtros['programa']) && $filtros['programa'] !== '' ? (int) $filtros['programa'] : null;
        $trayectoFiltro    = isset($filtros['trayecto']) && $filtros['trayecto'] !== '' ? $filtros['trayecto'] : null;
        $seccionFiltro     = isset($filtros['seccion']) && $filtros['seccion'] !== '' ? (int) $filtros['seccion'] : null;

        // Pre-cargar involucrados en lote (evita N+1 en tutor/representante)
        $proyectoIds = $proyectos->pluck('id')->filter()->values()->toArray();
        $involucradosMap = $this->cargarInvolucradosEnLote($proyectoIds);

        // Resolver docentes (profesores creadores del proyecto) en lote
        $cedulas = $proyectos->pluck('creador_cedula')->filter()->unique()->values()->toArray();
        $docentesMap = $this->resolverDocentesEnLote($cedulas);

        $filas = [];
        $maxIntegrantes = 1;

        $lapsoMasActual   = '';
        $lapsoCodigoMax   = 0;
        $pnfConteo        = [];

        foreach ($proyectos as $index => $proyecto) {
            $contexto = $this->resolverContextoEquipo($proyecto);

            // ── Filtrar por lapso ─────────────────────────────────────────
            $lapCodigo = (int) ($contexto['lap_codigo'] ?? 0);
            if ($lapsoFiltroCodigo !== null && $lapCodigo !== $lapsoFiltroCodigo) {
                continue;
            }

            // ── Filtrar por PNF ───────────────────────────────────────────
            $pnfSigla = mb_strtoupper(trim((string) ($contexto['pro_siglas'] ?? '')));
            if ($pnfFiltroSiglas !== null && $pnfSigla !== $pnfFiltroSiglas) {
                continue;
            }

            // ── Filtrar por programa (pro_codigo) ─────────────────────────
            $proCodigo = (int) ($contexto['pro_codigo'] ?? 0);
            if ($programaFiltro !== null && $proCodigo !== $programaFiltro) {
                continue;
            }

            // ── Filtrar por trayecto ──────────────────────────────────────
            $traCodigo = $contexto['tra_codigo'] ?? null;
            if ($trayectoFiltro !== null && (string) $traCodigo !== (string) $trayectoFiltro) {
                continue;
            }

            // ── Filtrar por sección ───────────────────────────────────────
            $secCodigo = (int) ($contexto['sec_codigo'] ?? 0);
            if ($seccionFiltro !== null && $secCodigo !== $seccionFiltro) {
                continue;
            }

            $integrantes = $this->resolverIntegrantes($proyecto);
            $inv = $involucradosMap[$proyecto->id] ?? ['tutor' => '', 'representante' => ''];
            $tutor = $inv['tutor'];
            $representante = $inv['representante'];
            $sede = $this->resolverSede($contexto);
            $lineaNombre = $proyecto->linea_investigacion?->nombre_investigacion ?? '';
            $localidad = $this->resolverLocalidadGeografica($proyecto);

            $maxIntegrantes = max($maxIntegrantes, count($integrantes));

            // ── Rastrear lapso más reciente ────────────────────────────────
            $lapNombre = trim((string) ($contexto['lap_nombre'] ?? ''));
            if ($lapCodigo > 0 && $lapCodigo > $lapsoCodigoMax) {
                $lapsoCodigoMax = $lapCodigo;
                $lapsoMasActual = $lapNombre;
            }

            // ── Rastrear PNF más frecuente ─────────────────────────────────
            if ($pnfSigla !== '') {
                $pnfConteo[$pnfSigla] = ($pnfConteo[$pnfSigla] ?? 0) + 1;
            }

            $proNombre = $contexto['pro_nombre'] ?? '';
            $proSiglas = $contexto['pro_siglas'] ?? '';

            $filas[] = [
                'pry_codigo'               => $proyecto->pry_codigo,
                'numero'                   => count($filas) + 1,
                'sede'                     => $sede,
                'pnf'                      => $proNombre !== '' ? $proNombre : $proSiglas,
                'trayecto'                 => $contexto['trayecto_nombre'] ?? '',
                'semestre'                 => $contexto['semestre_nombre'] ?? '',
                'titulo'                   => $proyecto->titulo ?? '',
                'resumen'                  => $proyecto->resumen ?? '',
                'linea_investigacion'      => $lineaNombre,
                'docente'                  => $docentesMap[$proyecto->creador_cedula] ?? '',
                'tutor_academico'          => $tutor,
                'representante_institucional' => $representante,
                'integrantes'              => $integrantes,
                'localidad_geografica'     => $localidad,
                'comunidad_beneficiada'    => $proyecto->cantidad_beneficiados ?? '',
                'resultado_socializacion'  => 'Aprobado',
            ];
        }

        $pnfPredominante = '';
        if (!empty($pnfConteo)) {
            arsort($pnfConteo);
            $pnfPredominante = (string) array_key_first($pnfConteo);
        }

        if ($pnfFiltroSiglas !== null) {
            $pnfPredominante = $pnfFiltroSiglas;
        }

        return [
            'maxIntegrantes'  => $maxIntegrantes,
            'filas'           => $filas,
            'lapsoMasActual'  => $lapsoMasActual,
            'pnfPredominante' => $pnfPredominante,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Privados / Protegidos
    // ─────────────────────────────────────────────────────────────

    /**
     * Obtiene los proyectos respetando los filtros del sistema.
     */
    protected function obtenerProyectos(array $filtros): Collection
    {
        $search = isset($filtros['search']) && $filtros['search'] !== '' ? trim($filtros['search']) : null;

        return Proyecto::with(['comunidad', 'linea_investigacion', 'comunidad.direccion.municipio.estado'])
            ->where('estado_validacion', 'aprobado')
            ->where('estado_logico', true)
            ->when($search !== null, function ($q) use ($search) {
                $termino = '%' . $search . '%';
                $q->where(function ($w) use ($search, $termino) {
                    // Resumen del proyecto
                    try {
                        $w->whereRaw('to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')) @@ plainto_tsquery(\'spanish\', ?)', [$search]);
                    } catch (\Throwable) {
                        $w->whereRaw('pry_resumen ILIKE ?', [$termino]);
                    }
                    // Referencia equipo
                    $w->orWhereRaw('pry_direccion_logica ILIKE ?', [$termino]);
                    // Nombre del grupo
                    $w->orWhereRaw('EXISTS (SELECT 1 FROM grupo_proyecto_modulo gpm WHERE (gpm.grp_identificador = proyectos.pry_direccion_logica OR gpm.grp_codigo::text = regexp_replace(proyectos.pry_direccion_logica, E\'^EQGRP:\', \'\')) AND gpm.grp_nombre ILIKE ?)', [$termino]);
                    // Miembros del grupo
                    $w->orWhereRaw('EXISTS (SELECT 1 FROM grupo_proyecto_modulo gpm WHERE (gpm.grp_identificador = proyectos.pry_direccion_logica OR gpm.grp_codigo::text = regexp_replace(proyectos.pry_direccion_logica, E\'^EQGRP:\', \'\')) AND gpm.grp_miembros::text ILIKE ?)', [$termino]);
                    // Profesor del grupo
                    $w->orWhereRaw('EXISTS (SELECT 1 FROM grupo_proyecto_modulo gpm WHERE (gpm.grp_identificador = proyectos.pry_direccion_logica OR gpm.grp_codigo::text = regexp_replace(proyectos.pry_direccion_logica, E\'^EQGRP:\', \'\')) AND (gpm.grp_creador_cedula::text ILIKE ? OR gpm.grp_contexto::text ILIKE ?))', [$termino, $termino]);
                    // Comunidad
                    $w->orWhereHas('comunidad', function ($qc) use ($termino) {
                        $qc->whereRaw('com_nombre ILIKE ?', [$termino]);
                    });
                    // Línea de investigación
                    $w->orWhereHas('linea_investigacion', function ($ql) use ($termino) {
                        $ql->whereRaw('lin_nombre_investigacion ILIKE ?', [$termino]);
                    });
                    // Metodología
                    $w->orWhereHas('metodologia', function ($qm) use ($termino) {
                        $qm->whereRaw('mei_nombre ILIKE ?', [$termino]);
                    });
                    // Tipo de investigación
                    $w->orWhereHas('tipo_investigacion', function ($qt) use ($termino) {
                        $qt->whereRaw('tin_nombre ILIKE ?', [$termino]);
                    });
                    // Objetivo de investigación
                    $w->orWhereHas('objetivo_investigacion', function ($oq) use ($termino) {
                        $oq->whereRaw('obi_nombre ILIKE ?', [$termino]);
                    });
                    // Vinculaciones – título
                    $w->orWhereHas('vinculaciones.tituloVinculacion', function ($vq) use ($termino) {
                        $vq->whereRaw('tiv_titulo ILIKE ?', [$termino]);
                    });
                    // Vinculaciones – comunidad vinculada
                    $w->orWhereHas('vinculaciones.comunidad', function ($vcq) use ($termino) {
                        $vcq->whereRaw('com_nombre ILIKE ?', [$termino]);
                    });
                    // Documentos – nombre del componente
                    $w->orWhereHas('documentos.componente', function ($dcq) use ($termino) {
                        $dcq->whereRaw('comp_nombre ILIKE ?', [$termino]);
                    });
                });
            })
            ->when(($filtros['comunidad'] ?? '') !== '', fn ($q) => $q->where('comunidad_id', (int) $filtros['comunidad']))
            ->when(($filtros['linea'] ?? '') !== '', fn ($q) => $q->where('linea_investigacion_id', (int) $filtros['linea']))
            ->when(($filtros['tipo_investigacion'] ?? '') !== '', fn ($q) => $q->where('tipo_investigacion_id', (int) $filtros['tipo_investigacion']))
            ->when(($filtros['metodologia'] ?? '') !== '', fn ($q) => $q->where('metodologia_id', (int) $filtros['metodologia']))
            ->orderBy('id')
            ->get();
    }

    /**
     * Resuelve el contexto académico (Sede, PNF, Trayecto, Sección, Lapso, Nombre del equipo)
     * desde la clave de equipo_ref del proyecto.
     *
     * @return array<string, string>
     */
    protected function resolverContextoEquipo(Proyecto $proyecto): array
    {
        $clave = $proyecto->equipo_ref ?? '';

        $defaults = [
            'lap_nombre'      => '',
            'sec_nombre'      => '',
            'pro_siglas'      => '',
            'pro_nombre'      => '',
            'pro_codigo'      => null,
            'tra_codigo'      => null,
            'trayecto_nombre' => '',
            'semestre_nombre' => '',
            'sed_nombre'      => '',
            'sed_siglas'      => '',
            'sec_codigo'      => null,
            'lap_codigo'      => null,
            'nombre_equipo'   => '',
        ];

        if ($clave === '') {
            return $defaults;
        }

        // ── Caso 1: Grupo registrado por identificador ──
        $gruposSvc = app(GrupoProyectoService::class);

        // Try lookup by identificador or EQGRP: id
        $grupo = null;
        if (!str_starts_with($clave, 'EQSEC:')) {
            $grupo = \App\Models\GrupoProyectoModulo::porIdentificador($clave);
        }
        if (!$grupo) {
            $partes = $gruposSvc->parsearClave($clave);
            if (($partes['tipo'] ?? '') === GrupoProyectoService::PREFIJO) {
                $grupo = $this->grupoRepo->find($partes['grp_codigo'] ?? 0);
            }
        }

        if ($grupo) {
            $ctx = $grupo->grp_contexto;
            if ($ctx) {
                $lapCodigo = (int) ($ctx['lap_codigo'] ?? 0);
                $secCodigo = (int) ($ctx['sec_codigo'] ?? 0);
                $sedNombre = '';
                $sedSiglas = '';

                if ($secCodigo > 0) {
                    [$sedNombre, $sedSiglas] = $this->obtenerSedeDeSeccion($secCodigo);
                }

                return array_merge($defaults, [
                    'lap_nombre'      => trim((string) ($ctx['lap_nombre'] ?? '')),
                    'sec_nombre'      => trim((string) ($ctx['sec_nombre'] ?? '')),
                    'pro_siglas'      => trim((string) ($ctx['pro_siglas'] ?? '')),
                    'pro_nombre'      => trim((string) ($ctx['pro_nombre'] ?? '')),
                    'pro_codigo'      => isset($ctx['pro_codigo']) ? (int) $ctx['pro_codigo'] : null,
                    'tra_codigo'      => isset($ctx['tra_codigo']) ? (int) $ctx['tra_codigo'] : null,
                    'trayecto_nombre' => trim((string) ($ctx['trayecto_nombre'] ?? $ctx['tra_nombre'] ?? '')),
                    'semestre_nombre' => trim((string) ($ctx['semestre_nombre'] ?? $ctx['sem_nombre'] ?? '')),
                    'sed_nombre'      => $sedNombre,
                    'sed_siglas'      => $sedSiglas,
                    'sec_codigo'      => $secCodigo > 0 ? $secCodigo : null,
                    'lap_codigo'      => $lapCodigo > 0 ? $lapCodigo : null,
                    'nombre_equipo'   => trim((string) ($grupo->grp_nombre ?? '')),
                ]);
            }
        }

        // ── Caso 2: Clave EQSEC:lapso:seccion ──
        $partesEq = $this->equipoSeccion->parsearClave($clave);
        if ($partesEq) {
            $lapCodigo = (int) $partesEq['lap_codigo'];
            $secCodigo = (int) $partesEq['sec_codigo'];

            $etiquetas = $this->equipoSeccion->etiquetasContexto($lapCodigo, $secCodigo);
            [$sedNombre, $sedSiglas] = $this->obtenerSedeDeSeccion($secCodigo);
            \Illuminate\Support\Facades\Log::info("Contexto para sec $secCodigo: Sede=$sedNombre, Trayecto=".($etiquetas['trayecto_nombre'] ?? 'vacio'));

            return array_merge($defaults, [
                'lap_nombre'      => $etiquetas['lap_nombre'] ?? '',
                'sec_nombre'      => $etiquetas['sec_nombre'] ?? '',
                'pro_siglas'      => $etiquetas['pro_siglas'] ?? '',
                'pro_nombre'      => $etiquetas['pro_nombre'] ?? '',
                'pro_codigo'      => $etiquetas['pro_codigo'] ?? null,
                'tra_codigo'      => $etiquetas['tra_codigo'] ?? null,
                'trayecto_nombre' => $etiquetas['trayecto_nombre'] ?? '',
                'semestre_nombre' => $etiquetas['semestre_nombre'] ?? '',
                'sed_nombre'      => $sedNombre,
                'sed_siglas'      => $sedSiglas,
                'sec_codigo'      => $secCodigo,
                'lap_codigo'      => $lapCodigo,
                'nombre_equipo'   => 'Sección ' . ($etiquetas['sec_nombre'] ?? $secCodigo),
            ]);
        }

        return $defaults;
    }

    /**
     * Obtiene el nombre de la sede a partir del código de sección.
     *
     * Busca en la BD académica la relación seccion → sede.
     * Si no existe la tabla sede, retorna cadenas vacías.
     *
     * @return array{0: string, 1: string}  [sed_nombre, sed_siglas]
     */
    protected function obtenerSedeDeSeccion(int $secCodigo): array
    {
        if (isset($this->sedeCache[$secCodigo])) {
            return $this->sedeCache[$secCodigo];
        }

        try {
            $conn = $this->equipoSeccion->academicConnection();

            $row = DB::connection($conn)
                ->table('seccion as sec')
                ->leftJoin('sede as sed', 'sed.sed_codigo', '=', 'sec.sec_cod_sede')
                ->where('sec.sec_codigo', $secCodigo)
                ->select([
                    DB::raw("TRIM(COALESCE(sed.sed_nombre, '')) as sed_nombre"),
                    DB::raw("TRIM(COALESCE(sed.sed_siglas, '')) as sed_siglas"),
                ])
                ->first();

            if ($row) {
                $this->sedeCache[$secCodigo] = [trim((string) $row->sed_nombre), trim((string) $row->sed_siglas)];
                return $this->sedeCache[$secCodigo];
            }
        } catch (\Throwable) {
            // La columna sec_cod_sede o la tabla sede puede no existir en simulación
        }

        $this->sedeCache[$secCodigo] = ['', ''];
        return $this->sedeCache[$secCodigo];
    }

    /**
     * Resuelve la lista de integrantes del equipo del proyecto.
     * La cantidad puede variar entre equipos.
     *
     * @return list<array{nombre: string, cedula: string}>
     */
    protected function resolverIntegrantes(Proyecto $proyecto): array
    {
        $clave = $proyecto->equipo_ref ?? '';
        if ($clave === '') {
            return [];
        }

        try {
            $miembros = $this->equipoSeccion->integrantes($clave);

            return $miembros->map(fn ($m) => [
                'nombre' => trim(($m->nombre ?? '') . ' ' . ($m->apellido ?? '')),
                'cedula' => trim((string) ($m->cedula ?? '')),
            ])->values()->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Busca el tutor académico entre los involucrados del proyecto.
     * Se considera tutor quien tenga un rol cuyo nombre contenga "tutor" (case-insensitive).
     */
    protected function resolverTutorAcademico(int $proyectoId): string
    {
        try {
            $conn = (string) config('dual_database.repositorio_connection', 'pgsql');

            $rows = DB::connection($conn)
                ->table('proyecto_involucrado as pi')
                ->join('involucrados as i', 'i.id', '=', 'pi.involucrado_id')
                ->join('involucrado_rol as ir', 'ir.proyecto_involucrado_id', '=', 'pi.id')
                ->join('roles_involucrados as ri', 'ri.id', '=', 'ir.rol_id')
                ->where('pi.proyecto_id', $proyectoId)
                ->whereRaw("LOWER(ri.nombre) LIKE ?", ['%tutor%'])
                ->select([
                    DB::raw("TRIM(i.nombre) as nombre"),
                    DB::raw("TRIM(i.apellido) as apellido"),
                    DB::raw("TRIM(i.cedula) as cedula"),
                ])
                ->distinct()
                ->get();

            if ($rows->isEmpty()) {
                return '';
            }

            return $rows->map(fn ($r) => trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')))->implode(', ');
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Resuelve la sede a mostrar en el reporte (nombre o siglas si existe).
     */
    protected function resolverSede(array $contexto): string
    {
        $nombre = $contexto['sed_nombre'] ?? '';
        $siglas = $contexto['sed_siglas'] ?? '';

        if ($nombre !== '') {
            return $siglas !== '' ? "{$siglas} - {$nombre}" : $nombre;
        }

        return $siglas !== '' ? $siglas : '—';
    }

    /**
     * Busca el representante institucional entre los involucrados del proyecto.
     * Se considera representante quien tenga un rol cuyo nombre contenga "representante" (case-insensitive).
     */
    protected function resolverRepresentanteInstitucional(int $proyectoId): string
    {
        try {
            $conn = (string) config('dual_database.repositorio_connection', 'pgsql');

            $rows = DB::connection($conn)
                ->table('proyecto_involucrado as pi')
                ->join('involucrados as i', 'i.id', '=', 'pi.involucrado_id')
                ->join('involucrado_rol as ir', 'ir.proyecto_involucrado_id', '=', 'pi.id')
                ->join('roles_involucrados as ri', 'ri.id', '=', 'ir.rol_id')
                ->where('pi.proyecto_id', $proyectoId)
                ->whereRaw("LOWER(ri.nombre) LIKE ?", ['%representante%'])
                ->select([
                    DB::raw("TRIM(i.nombre) as nombre"),
                    DB::raw("TRIM(i.apellido) as apellido"),
                    DB::raw("TRIM(i.cedula) as cedula"),
                ])
                ->distinct()
                ->get();

            if ($rows->isEmpty()) {
                return '';
            }

            return $rows->map(fn ($r) => trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')))->implode(', ');
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Resuelve la localidad geográfica donde se desarrolló el proyecto
     * a partir de la comunidad asociada: Estado, Municipio, Dirección.
     */
    protected function resolverLocalidadGeografica(Proyecto $proyecto): string
    {
        try {
            $comunidad = $proyecto->comunidad;
            if (!$comunidad) {
                return '';
            }

            $direccion = $comunidad->direccion;
            if (!$direccion) {
                return '';
            }

            $municipio = $direccion->municipio;
            $estado = $municipio?->estado;

            $partes = [];
            if ($estado) {
                $partes[] = trim($estado->est_nombre ?? '');
            }
            if ($municipio) {
                $partes[] = trim($municipio->mun_nombre ?? '');
            }
            $calle = trim($direccion->dir_calle ?? '');
            if ($calle !== '') {
                $partes[] = $calle;
            }

            return !empty($partes) ? implode(', ', $partes) : '';
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Carga todos los involucrados (tutor y representante) en una sola consulta
     * para evitar N+1 queries por proyecto.
     *
     * @param  int[]  $proyectoIds
     * @return array<int, array{tutor: string, representante: string}>
     */
    protected function cargarInvolucradosEnLote(array $proyectoIds): array
    {
        if (empty($proyectoIds)) {
            return [];
        }

        $resultado = [];

        try {
            $conn = (string) config('dual_database.repositorio_connection', 'pgsql');

            $rows = DB::connection($conn)
                ->table('proyecto_involucrado as pi')
                ->join('involucrados as i', 'i.id', '=', 'pi.involucrado_id')
                ->join('involucrado_rol as ir', 'ir.proyecto_involucrado_id', '=', 'pi.id')
                ->join('roles_involucrados as ri', 'ri.id', '=', 'ir.rol_id')
                ->whereIn('pi.proyecto_id', $proyectoIds)
                ->where(function ($q) {
                    $q->whereRaw("LOWER(ri.nombre) LIKE ?", ['%tutor%'])
                      ->orWhereRaw("LOWER(ri.nombre) LIKE ?", ['%representante%']);
                })
                ->select([
                    'pi.proyecto_id',
                    DB::raw("TRIM(i.nombre) as nombre"),
                    DB::raw("TRIM(i.apellido) as apellido"),
                    DB::raw("LOWER(ri.nombre) as rol_lower"),
                ])
                ->distinct()
                ->get();

            foreach ($rows as $row) {
                $pid = (int) $row->proyecto_id;
                if (!isset($resultado[$pid])) {
                    $resultado[$pid] = ['tutor' => '', 'representante' => ''];
                }
                $nombre = trim(($row->nombre ?? '') . ' ' . ($row->apellido ?? ''));
                if (str_contains($row->rol_lower ?? '', 'tutor') && $resultado[$pid]['tutor'] === '') {
                    $resultado[$pid]['tutor'] = $nombre;
                }
                if (str_contains($row->rol_lower ?? '', 'representante') && $resultado[$pid]['representante'] === '') {
                    $resultado[$pid]['representante'] = $nombre;
                }
            }
        } catch (\Throwable) {}

        return $resultado;
    }

    /**
     * Resuelve los nombres de los docentes (profesores creadores) desde pry_creador_cedula.
     *
     * @param  string[]  $cedulas
     * @return array<string, string>
     */
    protected function resolverDocentesEnLote(array $cedulas): array
    {
        if (empty($cedulas)) {
            return [];
        }

        $resultado = [];

        try {
            $rows = \App\Models\User::whereIn('usu_cedula', $cedulas)->get();

            foreach ($rows as $user) {
                $cedula = trim($user->usu_cedula);
                $nombre = trim($user->nombre ?? '');
                $apellido = trim($user->apellido ?? '');
                $resultado[$cedula] = $nombre . ($apellido !== '' ? ' ' . $apellido : '');
            }
        } catch (\Throwable) {
            // Si falla la consulta dejar docentes vacíos
        }

        return $resultado;
    }
}
