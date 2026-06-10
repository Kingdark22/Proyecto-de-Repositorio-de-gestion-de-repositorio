<?php

namespace App\Services;

use App\Helpers\DbHelper;
use App\Helpers\DualDatabase;
use App\Models\LapsoAcademico;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IntranetProfessorService
{
    protected const CACHE_TTL = 300;

    public function academicConnection(): string
    {
        return DbHelper::connection();
    }

    public function repositorioConnection(): string
    {
        return (string) config('dual_database.repositorio_connection', 'mysql');
    }

    /**
     * @return Collection<int, object{lap_codigo: int, lap_nombre: string}>
     */
    public function lapsosActivos(): Collection
    {
        $cacheKey = 'lapsos_activos_'.DbHelper::connection();

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(60), function () {
            try {
                $rows = DualDatabase::table('lapso_academico')
                    ->where('lap_estatus', config('proyecto_profesor.lapso_estatus_activo', 'A'))
                    ->orderByDesc('lap_codigo')
                    ->get(['lap_codigo', 'lap_nombre']);

                if (DbHelper::isUsingIntranet()) {
                    DualDatabase::mirrorAcademicRows('lapso_academico', $rows);
                }

                return $rows->reject(fn ($l) =>
                    empty(trim($l->lap_nombre ?? '')) || trim($l->lap_nombre) === 'No Regist.'
                )->values();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Error en lapsosActivos: " . $e->getMessage());
                return collect();
            }
        });
    }

    public function lapsoVigenteCodigo(): ?int
    {
        $lap = LapsoAcademico::vigente();

        return $lap ? (int) $lap->lap_codigo : null;
    }

    /**
     * Docente asignado a UC de proyecto en el lapso vigente: asignación activa más reciente
     * por sección + unidad (mayor sud_codigo), no históricos reemplazados.
     */
    public function esProfesorProyectoVigente(string $cedula, ?int $lapCodigo = null): bool
    {
        $lapCodigo = $lapCodigo ?? $this->lapsoVigenteCodigo();
        if ($lapCodigo === null) {
            return false;
        }

        return $this->esProfesorProyectoEnLapso($cedula, $lapCodigo);
    }

    /**
     * @param  array{programa?: int|null, trayecto?: int|null, seccion?: int|null}  $filtros
     */
    public function esProfesorProyectoEnLapso(string $cedula, ?int $lapCodigo = null, array $filtros = []): bool
    {
        $cedula = trim($cedula);
        if ($cedula === '' || $lapCodigo === null) {
            return false;
        }

        try {
            return $this->baseProfesorProyectoQuery($lapCodigo, $filtros)
                ->where('sud.sud_ced_docente', $cedula)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Claves EQSEC:lapso:sección donde el docente tiene UC de proyecto asignada.
     *
     * @return list<string>
     */
    public function clavesEquipoSeccionDocente(string $cedula, ?int $lapCodigo = null): array
    {
        $cedula = trim($cedula);
        $lapCodigo = $lapCodigo ?? $this->lapsoVigenteCodigo();
        if ($cedula === '' || $lapCodigo === null) {
            return [];
        }

        try {
            $equipos = app(IntranetEquipoSeccionService::class);

            return $this->baseProfesorProyectoQuery($lapCodigo)
                ->where('sud.sud_ced_docente', $cedula)
                ->select(['lap.lap_codigo', 'sec.sec_codigo'])
                ->distinct()
                ->get()
                ->map(fn ($r) => $equipos->construirClave((int) $r->lap_codigo, (int) $r->sec_codigo))
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public function esDocenteIntranet(string $cedula): bool
    {
        $cedula = trim($cedula);
        if ($cedula === '') {
            return false;
        }

        try {
            return DB::connection($this->academicConnection())
                ->table('seccion_unidad_docente')
                ->where('sud_ced_docente', $cedula)
                ->where("sud_ced_docente", "NOT LIKE", '%-%')
                ->whereRaw('LENGTH(sud_ced_docente) >= 6')
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @param  array{programa?: int|null, trayecto?: int|null, seccion?: int|null}  $filtros
     */
    public function esDocenteEnLapso(string $cedula, ?int $lapCodigo = null, array $filtros = []): bool
    {
        $cedula = trim($cedula);
        if ($cedula === '') {
            return false;
        }

        try {
            return $this->baseDocenteQuery($lapCodigo, $filtros)
                ->where('sud.sud_ced_docente', $cedula)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function configuracionModulo(string $cedula, ?int $lapCodigo = null): ?array
    {
        if (! $this->moduloTableExists()) {
            return null;
        }

        $query = DB::connection($this->repositorioConnection())
            ->table('profesor_proyecto_modulo')
            ->where('ppm_cedula', trim($cedula));

        if ($lapCodigo !== null) {
            $query->where('ppm_lap_codigo', $lapCodigo);
        }

        $row = $query->orderByDesc('ppm_codigo')->first();

        return $row ? (array) $row : null;
    }

    public function habilitadoEnModulo(string $cedula, ?int $lapCodigo = null): bool
    {
        if (! $this->esProfesorProyectoVigente($cedula, $lapCodigo)) {
            return false;
        }

        $cfg = $this->configuracionModulo($cedula, $lapCodigo);

        return $cfg ? (bool) ($cfg['ppm_habilitado'] ?? false) : false;
    }

    /**
     * @param  array{anio?: string, seccion?: string, sud_codigo?: int|null}  $datos
     */
    public function habilitarEnModulo(string $cedula, int $lapCodigo, array $datos): bool
    {
        if (! $this->esProfesorProyectoEnLapso($cedula, $lapCodigo)) {
            return false;
        }

        if (! $this->moduloTableExists()) {
            return false;
        }

        $cedula = trim($cedula);
        $payload = [
            'ppm_cedula' => $cedula,
            'ppm_lap_codigo' => $lapCodigo,
            'ppm_sud_codigo' => $datos['sud_codigo'] ?? null,
            'ppm_anio' => $datos['anio'] ?? null,
            'ppm_seccion' => $datos['seccion'] ?? null,
            'ppm_habilitado' => true,
            'updated_at' => now(),
        ];

        $existing = DB::connection($this->repositorioConnection())
            ->table('profesor_proyecto_modulo')
            ->where('ppm_cedula', $cedula)
            ->where('ppm_lap_codigo', $lapCodigo)
            ->first();

        if ($existing) {
            DB::connection($this->repositorioConnection())
                ->table('profesor_proyecto_modulo')
                ->where('ppm_codigo', $existing->ppm_codigo)
                ->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::connection($this->repositorioConnection())
                ->table('profesor_proyecto_modulo')
                ->insert($payload);
        }

        Cache::forget('profesor_configuraciones_indexadas');

        return true;
    }

    public function deshabilitarEnModulo(string $cedula, ?int $lapCodigo = null): void
    {
        if (! $this->moduloTableExists()) {
            return;
        }

        $query = DB::connection($this->repositorioConnection())
            ->table('profesor_proyecto_modulo')
            ->where('ppm_cedula', trim($cedula));

        if ($lapCodigo !== null) {
            $query->where('ppm_lap_codigo', $lapCodigo);
        }

        $query->update([
            'ppm_habilitado' => false,
            'updated_at' => now(),
        ]);

        Cache::forget('profesor_configuraciones_indexadas');
    }

    /**
     * @param  array{programa?: int|null, trayecto?: int|null, seccion?: int|null}  $filtros
     */
    public function paginateDocentes(
        string $search = '',
        ?int $lapCodigo = null,
        array $filtros = [],
        int $perPage = 10,
        int $page = 1,
    ): LengthAwarePaginator {
        try {
            $intranetQuery = $this->baseProfesorProyectoQuery($lapCodigo, $filtros)
                ->leftJoin('persona as p', 'p.per_cedula', '=', 'sud.sud_ced_docente')
                ->select('sud.sud_ced_docente')
                ->groupBy('sud.sud_ced_docente');

            if ($search !== '') {
                $term = '%' . $search . '%';
                $intranetQuery->where(function($q) use ($term) {
                    $q->where('sud.sud_ced_docente', 'LIKE', $term)
                      ->orWhere('p.per_nombres', 'LIKE', $term)
                      ->orWhere('p.per_apellidos', 'LIKE', $term)
                      ->orWhere('pro.pro_siglas', 'LIKE', $term)
                      ->orWhere('pro.pro_nombre', 'LIKE', $term)
                      ->orWhere('tra.tra_nombre', 'LIKE', $term)
                      ->orWhere('sec.sec_nombre', 'LIKE', $term)
                      ->orWhere('ucu.ucu_siglas', 'LIKE', $term)
                      ->orWhere('ucu.ucu_nombre', 'LIKE', $term);
                });
            }

            $intranetCedulas = $intranetQuery
                ->pluck('sud.sud_ced_docente')
                ->map(fn ($v) => trim($v))
                ->unique()
                ->values()
                ->all();

            $moduleCedulas = [];
            if ($this->moduloTableExists() && $lapCodigo !== null) {
                $modQuery = DB::connection($this->repositorioConnection())
                    ->table('profesor_proyecto_modulo')
                    ->where('ppm_habilitado', true)
                    ->where('ppm_lap_codigo', $lapCodigo)
                    ->select('ppm_cedula');

                if ($search !== '') {
                    $modQuery->where('ppm_cedula', 'LIKE', '%' . $search . '%');
                }

                $moduleCedulas = $modQuery
                    ->pluck('ppm_cedula')
                    ->map(fn ($v) => trim($v))
                    ->unique()
                    ->values()
                    ->all();
            }

            $allCedulas = array_values(array_unique(array_merge($intranetCedulas, $moduleCedulas)));
            sort($allCedulas);

            $total = count($allCedulas);

            if ($total === 0) {
                return new LengthAwarePaginator([], 0, $perPage, $page, ['path' => request()->url(), 'query' => request()->query()]);
            }

            $pageCedulas = array_slice($allCedulas, ($page - 1) * $perPage, $perPage);

            $rows = collect();
            $pageIntranetCedulas = array_intersect($pageCedulas, $intranetCedulas);

            if ($pageIntranetCedulas !== []) {
                $rows = $this->baseProfesorProyectoQuery($lapCodigo, $filtros)
                    ->leftJoin('persona as p', 'p.per_cedula', '=', 'sud.sud_ced_docente')
                    ->whereIn('sud.sud_ced_docente', $pageIntranetCedulas)
                    ->select([
                        'sud.sud_codigo',
                        'sud.sud_cod_seccion',
                        'sec.sec_codigo',
                        'sec.sec_nombre',
                        'lap.lap_codigo',
                        'lap.lap_nombre',
                        'ucu.ucu_siglas',
                        'ucu.ucu_nombre',
                        'pro.pro_siglas',
                        'pro.pro_nombre',
                        'tra.tra_nombre',
                    ])
                    ->selectRaw('sud.sud_ced_docente as cedula')
                    ->selectRaw('p.per_nombres as per_nombres')
                    ->selectRaw('p.per_apellidos as per_apellidos')
                    ->orderBy('sud.sud_ced_docente')
                    ->orderBy('pro.pro_siglas')
                    ->orderBy('tra.tra_nombre')
                    ->orderBy('sec.sec_nombre')
                    ->get();
            }

            $moduleOnlyCedulas = array_diff($pageCedulas, $intranetCedulas);
            $moduleOnlyRows = collect();

            if ($moduleOnlyCedulas !== []) {
                try {
                    $personas = DB::connection($this->academicConnection())
                        ->table('persona')
                        ->whereIn('per_cedula', $moduleOnlyCedulas)
                        ->select(['per_cedula', 'per_nombres', 'per_apellidos'])
                        ->get()
                        ->keyBy('per_cedula');

                    $lapso = DB::connection($this->academicConnection())
                        ->table('lapso_academico')
                        ->where('lap_codigo', $lapCodigo)
                        ->select(['lap_codigo', 'lap_nombre'])
                        ->first();

                    foreach ($moduleOnlyCedulas as $ced) {
                        $p = $personas->get($ced);
                        $moduleOnlyRows->push((object) [
                            'sud_codigo' => null,
                            'sud_cod_seccion' => null,
                            'sec_codigo' => null,
                            'sec_nombre' => null,
                            'lap_codigo' => $lapCodigo,
                            'lap_nombre' => $lapso->lap_nombre ?? 'N/A',
                            'ucu_siglas' => null,
                            'ucu_nombre' => null,
                            'pro_siglas' => null,
                            'pro_nombre' => null,
                            'tra_nombre' => null,
                            'cedula' => $ced,
                            'per_nombres' => $p->per_nombres ?? 'Docente',
                            'per_apellidos' => $p->per_apellidos ?? '',
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Error fetching module-only professor data: " . $e->getMessage());
                }
            }

            $rows = $rows->concat($moduleOnlyRows)->sortBy('cedula')->values();

            if (DbHelper::isUsingIntranet()) {
                app(IntranetSimulationMirrorService::class)->mirrorRows('seccion_unidad_docente', $rows);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error en paginateDocentes: " . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage, $page, ['path' => request()->url(), 'query' => request()->query()]);
        }

        $grouped = $this->agruparPorCedula($rows, $search);
        $items = $grouped->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Docentes con asignación activa en intranet (cualquier UC) en el lapso indicado.
     *
     * @return Paginator<int, object>
     */
    public function paginateDocentesActivos(
        string $search = '',
        ?int $lapCodigo = null,
        int $perPage = 10,
        int $page = 1,
    ): LengthAwarePaginator {
        $lapCodigo = $lapCodigo ?? $this->lapsoVigenteCodigo();

        try {
            $query = $this->baseDocenteQuery($lapCodigo)
                ->leftJoin('persona as p', 'p.per_cedula', '=', 'sud.sud_ced_docente')
                ->select([
                    'sud.sud_ced_docente as cedula',
                    'p.per_nombres as per_nombres',
                    'p.per_apellidos as per_apellidos',
                    'lap.lap_nombre as lapso_nombre',
                    'pro.pro_siglas',
                    'tra.tra_nombre',
                ])
                ->groupBy([
                    'sud.sud_ced_docente',
                    'p.per_nombres',
                    'p.per_apellidos',
                    'lap.lap_nombre',
                    'pro.pro_siglas',
                    'tra.tra_nombre',
                ])
                ->orderBy('per_apellidos')
                ->orderBy('per_nombres');

            if ($search !== '') {
                $term = '%' . $search . '%';
                $query->where(function($q) use ($term) {
                    $q->where('sud.sud_ced_docente', 'LIKE', $term)
                      ->orWhere('p.per_nombres', 'LIKE', $term)
                      ->orWhere('p.per_apellidos', 'LIKE', $term);
                });
            }

            $rows = $query->simplePaginate($perPage, ['*'], 'page', $page);

            if (DbHelper::isUsingIntranet()) {
                app(IntranetSimulationMirrorService::class)->mirrorRows('seccion_unidad_docente', $rows->getCollection());
            }

            return $rows;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error en paginateDocentesActivos: " . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage, $page);
        }
    }

    /**
     * @return Collection<int, object{pro_codigo: int, pro_siglas: string, pro_nombre: string}>
     */
    public function programasEnLapso(?int $lapCodigo): Collection
    {
        $cacheKey = 'todos_programas_' . DbHelper::connection();

        return Cache::remember($cacheKey, now()->addSeconds(self::CACHE_TTL), function () {
            try {
                return DB::connection($this->academicConnection())
                    ->table('programa')
                    ->select(['pro_codigo', 'pro_siglas', 'pro_nombre'])
                    ->orderBy('pro_siglas')
                    ->get();
            } catch (\Throwable) {
                return collect();
            }
        });
    }

    /**
     * @return Collection<int, object{tra_codigo: int, tra_nombre: string}>
     */
    public function trayectosEnLapso(?int $lapCodigo = null, ?int $programaCodigo = null): Collection
    {
        $cacheKey = 'todos_trayectos_' . ($programaCodigo ?? '0') . '_' . DbHelper::connection();

        return Cache::remember($cacheKey, now()->addSeconds(self::CACHE_TTL), function () use ($programaCodigo) {
            try {
                $conn = $this->academicConnection();
                $query = DB::connection($conn)
                    ->table('trayecto as tra')
                    ->select(['tra.tra_codigo', 'tra.tra_nombre']);

                if ($programaCodigo) {
                    $query->join('malla as mal', 'mal.mal_cod_trayecto', '=', 'tra.tra_codigo')
                        ->where('mal.mal_cod_programa', $programaCodigo);
                }

                return $query->distinct()
                    ->orderBy('tra.tra_nombre')
                    ->get();
            } catch (\Throwable) {
                return collect();
            }
        });
    }

    /**
     * @return Collection<int, object{sec_codigo: int, sec_nombre: string, pro_siglas: string|null, tra_nombre: string|null}>
     */
    public function seccionesEnLapso(?int $lapCodigo, ?int $programaCodigo = null, ?int $trayectoCodigo = null): Collection
    {
        if ($lapCodigo === null) {
            return collect();
        }

        $cacheKey = 'secciones_en_lapso_' . $lapCodigo . '_' . ($programaCodigo ?? '0') . '_' . ($trayectoCodigo ?? '0') . '_' . DbHelper::connection();

        return Cache::remember($cacheKey, now()->addSeconds(self::CACHE_TTL), function () use ($lapCodigo, $programaCodigo, $trayectoCodigo) {
            try {
                $conn = $this->academicConnection();
                $query = DB::connection($conn)
                    ->table('seccion as sec')
                    ->join('lapso_academico as lap', 'lap.lap_codigo', '=', 'sec.sec_cod_lapso_academico')
                    ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
                    ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
                    ->where('lap.lap_codigo', $lapCodigo)
                    ->select(['sec.sec_codigo', 'sec.sec_nombre', 'pro.pro_siglas']);

                $query->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
                    ->addSelect('tra.tra_nombre');

                if ($programaCodigo) {
                    $query->where('pro.pro_codigo', $programaCodigo);
                }

                if ($trayectoCodigo) {
                    $query->where('tra.tra_codigo', $trayectoCodigo);
                }

                return $query->distinct()
                    ->orderBy('sec.sec_nombre')
                    ->get();
            } catch (\Throwable) {
                return collect();
            }
        });
    }

    /**
     * @param  array{programa?: int|null, trayecto?: int|null, seccion?: int|null}  $filtros
     */
    protected function baseDocenteQuery(?int $lapCodigo = null, array $filtros = [])
    {
        $conn = $this->academicConnection();
        
        $query = DB::connection($conn)
            ->table('seccion_unidad_docente as sud')
            ->join('seccion as sec', 'sec.sec_codigo', '=', 'sud.sud_cod_seccion')
            ->join('lapso_academico as lap', 'lap.lap_codigo', '=', 'sec.sec_cod_lapso_academico')
            ->join('unidad_curricular as ucu', 'ucu.ucu_codigo', '=', 'sud.sud_cod_unidad')
            ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
            ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
            ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
            ->where("sud.sud_ced_docente", "NOT LIKE", '%-%')
            ->whereRaw('LENGTH(sud.sud_ced_docente) >= 6');

        if ($lapCodigo !== null) {
            $query->where('lap.lap_codigo', $lapCodigo);
        } else {
            $query->where('lap.lap_estatus', config('proyecto_profesor.lapso_estatus_activo', 'A'));
        }

        $sudActivo = config('proyecto_profesor.sud_estatus_activo');
        if ($sudActivo) {
            $query->where('sud.sud_estatus', $sudActivo);
        }

        if (isset($filtros['programa']) && $filtros['programa'] !== null && $filtros['programa'] !== '') {
            $query->where('pro.pro_codigo', (int) $filtros['programa']);
        }

        if (isset($filtros['trayecto']) && $filtros['trayecto'] !== null && $filtros['trayecto'] !== '') {
            $query->where('tra.tra_codigo', (int) $filtros['trayecto']);
        }

        if (isset($filtros['seccion']) && $filtros['seccion'] !== null && $filtros['seccion'] !== '') {
            $query->where('sec.sec_codigo', (int) $filtros['seccion']);
        }

        return $query;
    }

    /**
     * Docentes con asignación activa en UC de proyecto (lapso + malla/trayecto de la sección).
     *
     * @param  array{programa?: int|null, trayecto?: int|null, seccion?: int|null}  $filtros
     */
    protected function baseProfesorProyectoQuery(?int $lapCodigo = null, array $filtros = [])
    {
        $query = $this->baseDocenteQuery($lapCodigo, $filtros);
        $this->aplicarFiltroUnidadProyecto($query);
        $this->aplicarFiltroAsignacionVigenteReciente($query, $lapCodigo, $filtros);

        return $query;
    }

    /**
     * Restringe a la fila sud más reciente (mayor sud_codigo) activa por sección + UC de proyecto.
     *
     * @param  array{programa?: int|null, trayecto?: int|null, seccion?: int|null}  $filtros
     */
    protected function aplicarFiltroAsignacionVigenteReciente($query, ?int $lapCodigo, array $filtros): void
    {
        if (! config('proyecto_profesor.filtrar_sud_vigente_reciente', true)) {
            return;
        }

        $sub = $this->buildSudVigenteRecienteSubquery($lapCodigo, $filtros);

        $query->whereIn('sud.sud_codigo', function ($inner) use ($sub) {
            $inner->fromSub($sub, 'sud_vigente')->select('sud_codigo_vigente');
        });
    }

    /**
     * @param  array{programa?: int|null, trayecto?: int|null, seccion?: int|null}  $filtros
     */
    protected function buildSudVigenteRecienteSubquery(?int $lapCodigo, array $filtros = [])
    {
        $grupo = config('proyecto_profesor.sud_vigente_grupo', ['sud_cod_seccion', 'sud_cod_unidad']);
        $columnasGrupo = array_map(
            fn (string $col) => 'sud_v.'.$col,
            $grupo
        );

        $sub = DB::connection($this->academicConnection())
            ->table('seccion_unidad_docente as sud_v')
            ->join('seccion as sec_v', 'sec_v.sec_codigo', '=', 'sud_v.sud_cod_seccion')
            ->join('lapso_academico as lap_v', 'lap_v.lap_codigo', '=', 'sec_v.sec_cod_lapso_academico')
            ->join('unidad_curricular as ucu_v', 'ucu_v.ucu_codigo', '=', 'sud_v.sud_cod_unidad')
            ->leftJoin('malla as mal_v', 'mal_v.mal_codigo', '=', 'sec_v.sec_cod_malla')
            ->leftJoin('programa as pro_v', 'pro_v.pro_codigo', '=', 'mal_v.mal_cod_programa')
            ->leftJoin('trayecto as tra_v', 'tra_v.tra_codigo', '=', 'mal_v.mal_cod_trayecto')
            ->where("sud_v.sud_ced_docente", "NOT LIKE", '%-%')
            ->whereRaw('LENGTH(sud_v.sud_ced_docente) >= 6');

        if ($lapCodigo !== null) {
            $sub->where('lap_v.lap_codigo', $lapCodigo);
        } else {
            $sub->where('lap_v.lap_estatus', config('proyecto_profesor.lapso_estatus_activo', 'A'));
        }

        $sudActivo = config('proyecto_profesor.sud_estatus_activo');
        if ($sudActivo) {
            $sub->where('sud_v.sud_estatus', $sudActivo);
        }

        if (isset($filtros['programa']) && $filtros['programa'] !== null && $filtros['programa'] !== '') {
            $sub->where('pro_v.pro_codigo', (int) $filtros['programa']);
        }

        if (isset($filtros['trayecto']) && $filtros['trayecto'] !== null && $filtros['trayecto'] !== '') {
            $sub->where('tra_v.tra_codigo', (int) $filtros['trayecto']);
        }

        if (isset($filtros['seccion']) && $filtros['seccion'] !== null && $filtros['seccion'] !== '') {
            $sub->where('sec_v.sec_codigo', (int) $filtros['seccion']);
        }

        $this->aplicarFiltroUnidadProyecto($sub, 'ucu_v');

        return $sub
            ->groupBy($columnasGrupo)
            ->selectRaw('MAX(sud_v.sud_codigo) as sud_codigo_vigente');
    }

    protected function aplicarFiltroUnidadProyecto($query, string $aliasUnidad = 'ucu'): void
    {
        $prefijos = config('proyecto_profesor.unidad_siglas_prefijos', []);
        $patrones = config('proyecto_profesor.unidad_nombre_patrones', []);

        if ($prefijos === [] && $patrones === []) {
            return;
        }

        $colSiglas = $aliasUnidad.'.ucu_siglas';
        $colNombre = $aliasUnidad.'.ucu_nombre';

        $query->where(function ($q) use ($prefijos, $patrones, $colSiglas, $colNombre) {
            foreach ($prefijos as $prefijo) {
                $prefijo = trim((string) $prefijo);
                if ($prefijo !== '') {
                    $q->orWhereRaw('TRIM('.$colSiglas.') LIKE ?', [$prefijo.'%']);
                }
            }
            foreach ($patrones as $patron) {
                $patron = trim((string) $patron);
                if ($patron !== '') {
                    $q->orWhereRaw('UPPER(TRIM('.$colNombre.')) LIKE ?', ['%'.$patron.'%']);
                }
            }
        });
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return Collection<int, object>
     */
    protected function agruparPorCedula(Collection $rows, string $search): Collection
    {
        $search = mb_strtolower(trim($search));
        $configs = $this->configuracionesIndexadas();

        return $rows
            ->groupBy(fn ($r) => trim((string) $r->cedula) . ':' . (int) $r->lap_codigo)
            ->map(function (Collection $asignaciones, string $groupKey) use ($configs) {
                $cedula = explode(':', $groupKey, 2)[0];
                $primera = $asignaciones->first();
                $lapCodigo = (int) $primera->lap_codigo;
                $cfg = $configs[$cedula . ':' . $lapCodigo] ?? $configs[$cedula . ':'] ?? null;

                return (object) [
                    'cedula' => $cedula,
                    'nombre' => trim($primera->per_nombres ?? '') ?: 'Docente',
                    'apellido' => trim($primera->per_apellidos ?? ''),
                    'lap_codigo' => $lapCodigo,
                    'lapso_nombre' => $primera->lap_nombre,
                    'programa_siglas' => trim($primera->pro_siglas ?? ''),
                    'trayecto_nombre' => trim($primera->tra_nombre ?? ''),
                    'asignaciones' => $asignaciones->map(fn ($a) => (object) [
                        'sud_codigo' => $a->sud_codigo,
                        'seccion' => trim($a->sec_nombre ?? ''),
                        'seccion_codigo' => $a->sec_codigo ?? null,
                        'unidad_siglas' => trim($a->ucu_siglas ?? ''),
                        'unidad_nombre' => trim($a->ucu_nombre ?? ''),
                        'programa_siglas' => trim($a->pro_siglas ?? ''),
                        'programa_nombre' => trim($a->pro_nombre ?? ''),
                        'trayecto_nombre' => trim($a->tra_nombre ?? ''),
                    ])->values(),
                    'habilitado_modulo' => $cfg ? (bool) $cfg->ppm_habilitado : false,
                    'ppm_anio' => $cfg->ppm_anio ?? null,
                    'ppm_seccion' => $cfg->ppm_seccion ?? null,
                    'sud_codigo' => $cfg->ppm_sud_codigo ?? $primera->sud_codigo,
                ];
            })
            ->filter(function ($doc) use ($search) {
                if ($search === '') {
                    return true;
                }

                $haystack = mb_strtolower(
                    $doc->nombre . ' ' . $doc->apellido . ' ' . $doc->cedula . ' ' . $doc->lapso_nombre
                );

                foreach ($doc->asignaciones as $a) {
                    $haystack .= ' ' . mb_strtolower(
                        $a->seccion . ' ' . $a->unidad_siglas . ' ' . $a->programa_siglas . ' ' . $a->trayecto_nombre
                    );
                }

                return str_contains($haystack, $search);
            })
            ->values();
    }

    /**
     * @return array<string, object>
     */
    protected function configuracionesIndexadas(): array
    {
        if (! $this->moduloTableExists()) {
            return [];
        }

        return Cache::remember('profesor_configuraciones_indexadas', now()->addSeconds(300), function () {
            $index = [];
            $rows = DB::connection($this->repositorioConnection())
                ->table('profesor_proyecto_modulo')
                ->get();

            foreach ($rows as $row) {
                $key = trim($row->ppm_cedula) . ':' . (int) $row->ppm_lap_codigo;
                $index[$key] = $row;
            }

            return $index;
        });
    }

    public function moduloTableExists(): bool
    {
        try {
            return Schema::connection($this->repositorioConnection())->hasTable('profesor_proyecto_modulo');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array{selectedYear: array<string, string>, selectedSection: array<string, string>}
     */
    public function cargarSeleccionesFormulario(): array
    {
        $selectedYear = [];
        $selectedSection = [];

        if (! $this->moduloTableExists()) {
            return compact('selectedYear', 'selectedSection');
        }

        $configs = DB::connection($this->repositorioConnection())
            ->table('profesor_proyecto_modulo')
            ->where('ppm_habilitado', true)
            ->get();

        foreach ($configs as $cfg) {
            $cedula = trim((string) $cfg->ppm_cedula);
            if ($cfg->ppm_anio) {
                $selectedYear[$cedula] = $cfg->ppm_anio;
            }
            if ($cfg->ppm_seccion) {
                $selectedSection[$cedula] = $cfg->ppm_seccion;
            }
        }

        return compact('selectedYear', 'selectedSection');
    }

    /**
     * @param  array{search?: string, lapso?: int|null, programa?: int|null, trayecto?: int|null, seccion?: int|null}  $params
     * @return array<string, mixed>
     */
    public function datosVistaGestion(array $params = []): array
    {
        $lapCodigo = (int) ($params['lapso'] ?? $this->lapsoVigenteCodigo());
        $programaCodigo = $params['programa'] ?? null;
        $trayectoCodigo = $params['trayecto'] ?? null;
        $page = (int) ($params['page'] ?? 1);

        $filtros = array_filter([
            'programa' => $programaCodigo,
            'trayecto' => $trayectoCodigo,
            'seccion' => $params['seccion'] ?? null,
        ]);

        $trayectosCatalogo = $this->trayectosEnLapso($lapCodigo, $programaCodigo);

        return [
            'docentes' => $this->paginateDocentes($params['search'] ?? '', $lapCodigo, $filtros, 10, $page),
            'lapsos' => $this->lapsosActivos(),
            'programas' => $this->programasEnLapso($lapCodigo),
            'trayectosCatalogo' => $trayectosCatalogo,
            'secciones' => $this->seccionesEnLapso($lapCodigo, $programaCodigo, $trayectoCodigo),
            'trayectosHabilitar' => $trayectosCatalogo->isNotEmpty()
                ? $trayectosCatalogo->pluck('tra_nombre')->unique()->values()
                : collect(config('proyecto_profesor.trayectos', [])),
            'intranetDisponible' => DbHelper::isUsingIntranet(),
        ];
    }

    /**
     * @param  array{programa?: int|null, trayecto?: int|null, seccion?: int|null}  $filtrosIntranet
     * @param  array{anio?: string, seccion?: string}  $habilitarDatos
     * @return array{ok: bool, flash: string, message: string}
     */
    public function alternarHabilitacionModulo(
        string $cedula,
        int $lapCodigo,
        array $filtrosIntranet,
        array $habilitarDatos,
    ): array {
        $cedula = trim($cedula);

        if ($lapCodigo <= 0) {
            return ['ok' => false, 'flash' => 'message_error', 'message' => 'Seleccione un lapso académico activo.'];
        }

        if (! $this->esProfesorProyectoEnLapso($cedula, $lapCodigo, $filtrosIntranet)) {
            return ['ok' => false, 'flash' => 'message_error', 'message' => 'El docente no está asignado a la UC de Proyecto en ese lapso y malla (intranet).'];
        }

        if ($this->habilitadoEnModulo($cedula, $lapCodigo)) {
            $this->deshabilitarEnModulo($cedula, $lapCodigo);

            return ['ok' => true, 'flash' => 'message', 'message' => 'Profesor de proyecto deshabilitado en el módulo.', 'deshabilitado' => true];
        }

        if (empty($habilitarDatos['anio'] ?? '')) {
            return ['ok' => false, 'flash' => 'message_error', 'message' => 'Debe seleccionar un año (trayecto) para el proyecto.'];
        }

        if (empty($habilitarDatos['seccion'] ?? '')) {
            return ['ok' => false, 'flash' => 'message_error', 'message' => 'Debe indicar la sección académica.'];
        }

        $this->habilitarEnModulo($cedula, $lapCodigo, [
            'anio' => $habilitarDatos['anio'],
            'seccion' => $habilitarDatos['seccion'],
        ]);

        return ['ok' => true, 'flash' => 'message', 'message' => 'Profesor habilitado como evaluador de proyecto en este lapso.', 'deshabilitado' => false];
    }

}
