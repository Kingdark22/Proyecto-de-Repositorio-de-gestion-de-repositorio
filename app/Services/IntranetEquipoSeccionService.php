<?php

namespace App\Services;

use App\Helpers\DbHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Equipo = encapsulación del grupo de proyecto para el repositorio.
 *
 * - Grupo de proyecto: estudiantes (líder/autor) que elaboran el expediente en intranet.
 * - Equipo: vista/clave usada en pry_direccion_logica (EQSEC:lapso:sección o, si existe
 *   grupo_proyecto_estudiante, roles explícitos de ese grupo).
 *
 * No usa tablas locales equipos / equipo_estudiante.
 */
class IntranetEquipoSeccionService
{
    public const ROL_LIDER = 1;

    public const ROL_AUTOR = 2;

    public const PREFIJO_REF = 'EQSEC';

    public function tablaGrupoProyectoExiste(): bool
    {
        return $this->tablaGrupoProyectoExisteInternal();
    }

    public function academicConnection(): string
    {
        return DbHelper::connection();
    }

    public function construirClave(int $lapCodigo, int $secCodigo): string
    {
        return self::PREFIJO_REF.':'.$lapCodigo.':'.$secCodigo;
    }

    /**
     * @return array{lap_codigo: int, sec_codigo: int}|null
     */
    public function parsearClave(?string $clave): ?array
    {
        if ($clave === null || $clave === '') {
            return null;
        }

        $borrador = app(GrupoProyectoService::class)->parsearClave($clave);
        if (($borrador['tipo'] ?? '') === GrupoProyectoService::PREFIJO) {
            $grupo = app(GrupoProyectoService::class)->obtener((int) $borrador['grp_codigo']);

            return $grupo
                ? ['lap_codigo' => $grupo->lap_codigo, 'sec_codigo' => $grupo->sec_codigo]
                : null;
        }

        if (preg_match('/^'.self::PREFIJO_REF.':(\d+):(\d+)$/', $clave, $m)) {
            return ['lap_codigo' => (int) $m[1], 'sec_codigo' => (int) $m[2]];
        }

        return null;
    }

    protected function inscripcionActivaEstatus(): array
    {
        return config('equipo_proyecto.inscripcion_estatus_activo', ['A']);
    }

    protected function baseInscripcionQuery()
    {
        $query = DB::connection($this->academicConnection())
            ->table('inscripcion as ins')
            ->join('seccion_unidad_docente as sud', 'sud.sud_codigo', '=', 'ins.ins_cod_seccion_unidad_docente')
            ->join('seccion as sec', 'sec.sec_codigo', '=', 'sud.sud_cod_seccion')
            ->join('lapso_academico as lap', 'lap.lap_codigo', '=', 'sec.sec_cod_lapso_academico')
            ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
            ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
            ->whereIn('ins.ins_estatus', $this->inscripcionActivaEstatus())
            ->where('lap.lap_estatus', config('proyecto_profesor.lapso_estatus_activo', 'A'));

        $query->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto');

        return $query;
    }

    /**
     * Si existe grupo_proyecto_estudiante, valida rol ahí; si no, basta inscripción activa en la sección.
     */
    public function estudiantePerteneceEquipo(string $cedula, string $equipoClave, ?int $rolRequerido = null): bool
    {
        $cedula = trim($cedula);
        if ($cedula === '') {
            return false;
        }

        $borrador = app(GrupoProyectoService::class);
        if (str_starts_with($equipoClave, GrupoProyectoService::PREFIJO.':')) {
            return app(GrupoProyectoService::class)->estudianteEnGrupo($cedula, $equipoClave, $rolRequerido);
        }

        $partes = $this->parsearClave($equipoClave);
        if ($partes === null) {
            return false;
        }

        if ($rolRequerido !== null && $this->tablaGrupoProyectoExisteInternal()) {
            return $this->perteneceGrupoProyecto($cedula, $partes, $rolRequerido);
        }

        try {
            return $this->baseInscripcionQuery()
                ->whereRaw('TRIM(ins.ins_cedula) = ?', [$cedula])
                ->where('lap.lap_codigo', $partes['lap_codigo'])
                ->where('sec.sec_codigo', $partes['sec_codigo'])
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public function estudiantePuedeRegistrar(string $cedula, ?int $lapCodigo = null): bool
    {
        $gruposSvc = app(GrupoProyectoService::class);
        if ($gruposSvc->tablaDisponible()) {
            $esLider = $gruposSvc->listar(['lapso' => $lapCodigo])
                ->contains(fn ($g) => collect($g->miembros)->contains(
                    fn ($m) => trim((string) ($m['cedula'] ?? '')) === trim($cedula)
                        && (int) ($m['rol_id'] ?? 0) === self::ROL_LIDER
                ));

            if ($esLider) {
                return true;
            }
        }

        if ($this->tablaGrupoProyectoExisteInternal()) {
            try {
                $q = DB::connection($this->academicConnection())
                    ->table('grupo_proyecto_estudiante')
                    ->whereRaw('TRIM(gpe_ced_estudiante) = ?', [trim($cedula)])
                    ->where('gpe_rol_id', self::ROL_LIDER);

                if ($lapCodigo !== null) {
                    $q->where('gpe_cod_lapso', $lapCodigo);
                }

                return $q->exists();
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }

    /**
     * Equipos (secciones) donde el estudiante tiene inscripción activa.
     *
     * @return Collection<int, object>
     */
    public function equiposDelEstudiante(string $cedula, ?int $lapCodigo = null): Collection
    {
        $cedula = trim($cedula);
        if ($cedula === '') {
            return collect();
        }

        try {
            $conn = $this->academicConnection();
            $query = $this->baseInscripcionQuery()
                ->whereRaw('TRIM(ins.ins_cedula) = ?', [$cedula])
                ->select([
                    'sec.sec_codigo',
                    'sec.sec_nombre',
                    'lap.lap_codigo',
                    'lap.lap_nombre',
                    'pro.pro_siglas',
                    'pro.pro_nombre',
                    'pro.pro_codigo',
                ])
                ->addSelect('tra.tra_nombre');

            if ($lapCodigo !== null) {
                $query->where('lap.lap_codigo', $lapCodigo);
            }

            $filas = $query
                ->distinct()
                ->orderByDesc('lap.lap_codigo')
                ->orderBy('sec.sec_nombre')
                ->get();

            app(IntranetSimulationMirrorService::class)->mirrorUserContext($cedula);

            $borradores = app(GrupoProyectoService::class)
                ->listar(['lapso' => $lapCodigo])
                ->filter(fn ($g) => collect($g->miembros)->contains(
                    fn ($m) => trim((string) ($m['cedula'] ?? '')) === $cedula
                ));

            $equiposRegistrados = $borradores->map(fn ($g) => $this->mapearEquipoGrupoRegistrado($g, $cedula));

            $porSeccion = $filas->map(function ($r) use ($cedula) {
                $r->integrantes = $this->integrantes(
                    $this->construirClave((int) $r->lap_codigo, (int) $r->sec_codigo)
                )->count();

                return $this->mapearEquipo($r, $cedula);
            });

            return $equiposRegistrados->concat($porSeccion)->unique('clave')->values();
        } catch (\Throwable) {
            return collect();
        }
    }

    /**
     * Listado para administrador: una fila por sección/lapso con al menos un inscrito activo.
     *
     * @param  array{lapso?: int|null, programa?: int|null, seccion?: int|null, busqueda?: string}  $filtros
     * @return Collection<int, object>
     */
    public function listarEquiposPorSeccion(array $filtros = []): Collection
    {
        if (empty($filtros['lapso'])) {
            return collect();
        }

        try {
            $conn = $this->academicConnection();
            $query = $this->baseInscripcionQuery()
                ->select([
                    'sec.sec_codigo',
                    'sec.sec_nombre',
                    'lap.lap_codigo',
                    'lap.lap_nombre',
                    'pro.pro_siglas',
                    'pro.pro_nombre',
                ])
                ->selectRaw('COUNT(DISTINCT TRIM(ins.ins_cedula)) as integrantes')
                ->addSelect('tra.tra_nombre');

            $groupBy = [
                'sec.sec_codigo',
                'sec.sec_nombre',
                'lap.lap_codigo',
                'lap.lap_nombre',
                'pro.pro_siglas',
                'pro.pro_nombre',
                'tra.tra_nombre',
            ];

            if (! empty($filtros['lapso'])) {
                $query->where('lap.lap_codigo', (int) $filtros['lapso']);
            }
            if (! empty($filtros['programa'])) {
                $query->where('pro.pro_codigo', (int) $filtros['programa']);
            }
            if (! empty($filtros['seccion'])) {
                $query->where('sec.sec_codigo', (int) $filtros['seccion']);
            }
            if (! empty($filtros['busqueda'])) {
                $term = '%'.mb_strtolower(trim($filtros['busqueda'])).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(TRIM(sec.sec_nombre)) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(TRIM(pro.pro_siglas)) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(TRIM(lap.lap_nombre)) LIKE ?', [$term]);
                });
            }

            return $query
                ->groupBy($groupBy)
                ->orderByDesc('lap.lap_codigo')
                ->orderBy('sec.sec_nombre')
                ->get()
                ->map(fn ($r) => $this->mapearEquipo($r));
        } catch (\Throwable) {
            return collect();
        }
    }

    /**
     * @return Collection<int, object{cedula: string, nombre: string, apellido: string, rol: string}>
     */
    public function integrantes(string $equipoClave): Collection
    {
        $cacheKey = 'eq_int_' . md5($equipoClave . '_' . $this->academicConnection());

        return Cache::remember($cacheKey, 300, function () use ($equipoClave) {
        if (str_starts_with($equipoClave, GrupoProyectoService::PREFIJO.':')) {
            return app(GrupoProyectoService::class)->integrantesDesdeClave($equipoClave);
        }

        $partes = $this->parsearClave($equipoClave);
        if ($partes === null) {
            return collect();
        }

        if ($this->tablaGrupoProyectoExisteInternal()) {
            try {
                $rows = DB::connection($this->academicConnection())
                    ->table('grupo_proyecto_estudiante as gpe')
                    ->join('persona as p', DB::raw('TRIM(p.per_cedula)'), '=', DB::raw('TRIM(gpe.gpe_ced_estudiante)'))
                    ->where('gpe.gpe_cod_seccion', $partes['sec_codigo'])
                    ->when($partes['lap_codigo'], fn ($q) => $q->where('gpe.gpe_cod_lapso', $partes['lap_codigo']))
                    ->selectRaw('TRIM(gpe.gpe_ced_estudiante) as cedula')
                    ->selectRaw('TRIM(p.per_nombres) as nombre')
                    ->selectRaw('TRIM(p.per_apellidos) as apellido')
                    ->selectRaw('gpe.gpe_rol_id as rol_id')
                    ->distinct()
                    ->get();

                if ($rows->isNotEmpty()) {
                    return $rows->map(fn ($r) => (object) [
                        'cedula' => trim((string) $r->cedula),
                        'nombre' => trim((string) $r->nombre),
                        'apellido' => trim((string) $r->apellido),
                        'rol' => (int) $r->rol_id === self::ROL_LIDER ? 'Líder' : 'Autor',
                    ]);
                }
            } catch (\Throwable) {
                //
            }
        }

        try {
            $rows = $this->baseInscripcionQuery()
                ->join('persona as p', DB::raw('TRIM(p.per_cedula)'), '=', DB::raw('TRIM(ins.ins_cedula)'))
                ->where('lap.lap_codigo', $partes['lap_codigo'])
                ->where('sec.sec_codigo', $partes['sec_codigo'])
                ->selectRaw('TRIM(ins.ins_cedula) as cedula')
                ->selectRaw('TRIM(p.per_nombres) as nombre')
                ->selectRaw('TRIM(p.per_apellidos) as apellido')
                ->distinct()
                ->orderBy('apellido')
                ->orderBy('nombre')
                ->get();

            $lideres = $this->cedulasLideresEnSeccion($partes);

            return $rows->map(function ($r) use ($lideres) {
                $cedula = trim((string) $r->cedula);

                return (object) [
                    'cedula' => $cedula,
                    'nombre' => trim((string) $r->nombre),
                    'apellido' => trim((string) $r->apellido),
                    'rol' => in_array($cedula, $lideres, true) ? 'Líder' : 'Integrante',
                ];
            });
        } catch (\Throwable) {
            return collect();
        }
        });
    }

    public function resumenEquipo(?string $equipoClave): string
    {
        if (str_starts_with((string) $equipoClave, GrupoProyectoService::PREFIJO.':')) {
            $g = app(GrupoProyectoService::class)->obtenerPorClave($equipoClave);

            return $g ? 'Grupo '.$g->nombre.' ('.$g->integrantes.' integrantes)' : '—';
        }

        $partes = $this->parsearClave($equipoClave);
        if ($partes === null) {
            return '—';
        }

        try {
            $row = DB::connection($this->academicConnection())
                ->table('seccion as sec')
                ->join('lapso_academico as lap', 'lap.lap_codigo', '=', 'sec.sec_cod_lapso_academico')
                ->where('sec.sec_codigo', $partes['sec_codigo'])
                ->where('lap.lap_codigo', $partes['lap_codigo'])
                ->select(['sec.sec_nombre', 'lap.lap_nombre'])
                ->first();

            if (! $row) {
                return 'Sección '.$partes['sec_codigo'];
            }

            return 'Sección '.trim($row->sec_nombre).' · '.trim($row->lap_nombre);
        } catch (\Throwable) {
            return 'Sección '.$partes['sec_codigo'];
        }
    }

    public function programasEnLapso(?int $lapCodigo): Collection
    {
        $conn = $this->academicConnection();
        $cacheKey = 'equipos_programas_lapso_v2_'.($lapCodigo ?? '0').'_'.$conn;

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($lapCodigo, $conn) {
            try {
                if ($lapCodigo === null) {
                    return DB::connection($conn)
                        ->table('programa')
                        ->select(['pro_codigo', 'pro_siglas', 'pro_nombre'])
                        ->orderBy('pro_siglas')
                        ->get();
                }

                return DB::connection($conn)
                    ->table('seccion as sec')
                    ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
                    ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
                    ->where('sec.sec_cod_lapso_academico', $lapCodigo)
                    ->whereNotNull('pro.pro_codigo')
                    ->select(['pro.pro_codigo', 'pro.pro_siglas', 'pro.pro_nombre'])
                    ->distinct()
                    ->orderBy('pro.pro_siglas')
                    ->get();
            } catch (\Throwable) {
                return DB::connection($conn)
                    ->table('programa')
                    ->select(['pro_codigo', 'pro_siglas', 'pro_nombre'])
                    ->orderBy('pro_siglas')
                    ->get();
            }
        });
    }

    public function seccionesEnLapso(?int $lapCodigo, ?int $programaCodigo = null): Collection
    {
        if ($lapCodigo === null) {
            return collect();
        }

        $cacheKey = 'equipos_secciones_v2_'.$lapCodigo.'_'.($programaCodigo ?? '0').'_'.DbHelper::connection();

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($lapCodigo, $programaCodigo) {
            try {
                $conn = $this->academicConnection();
                $query = DB::connection($conn)
                    ->table('seccion as sec')
                    ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
                    ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
                    ->where('sec.sec_cod_lapso_academico', $lapCodigo)
                    ->select(['sec.sec_codigo', 'sec.sec_nombre', 'pro.pro_siglas']);

                $query->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
                    ->addSelect('tra.tra_nombre');

                if ($programaCodigo) {
                    $query->where('pro.pro_codigo', $programaCodigo);
                }

                return $query->distinct()
                    ->orderBy('sec.sec_nombre')
                    ->get();
            } catch (\Throwable) {
                return collect();
            }
        });
    }

    public function trayectosEnLapso(?int $lapCodigo = null, ?int $programaCodigo = null): Collection
    {
        $cacheKey = 'equipos_trayectos_v3_'.($lapCodigo ?? '0').'_'.($programaCodigo ?? '0').'_'.DbHelper::connection();

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($lapCodigo, $programaCodigo) {
            try {
                $query = DB::connection($this->academicConnection())
                    ->table('trayecto as tra')
                    ->select(['tra.tra_codigo', 'tra.tra_nombre']);

                if ($programaCodigo) {
                    $query->join('malla as mal', 'mal.mal_cod_trayecto', '=', 'tra.tra_codigo')
                        ->where('mal.mal_cod_programa', $programaCodigo);
                }

                // If lapso is provided, filter secciones in that lapso
                if ($lapCodigo) {
                    $query->join('seccion as sec', function ($join) use ($lapCodigo) {
                        $join->on('sec.sec_cod_malla', 'mal.mal_codigo')
                            ->where('sec.sec_cod_lapso_academico', $lapCodigo);
                    });
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
     * Etiquetas de lapso, PNF y sección (solo lectura intranet; para guardar en grp_contexto o mostrar).
     *
     * @return array{lap_nombre: string, sec_nombre: string, pro_siglas: string, pro_nombre: string, tra_codigo: int|null, trayecto_nombre: string}
     */
    public function etiquetasContexto(int $lapCodigo, int $secCodigo, ?int $proCodigo = null): array
    {
        if ($lapCodigo <= 0 || $secCodigo <= 0) {
            return ['lap_nombre' => '', 'sec_nombre' => '', 'pro_siglas' => '', 'pro_nombre' => '', 'tra_codigo' => null, 'trayecto_nombre' => ''];
        }

        $cacheKey = 'eq_ctx_' . $lapCodigo . '_' . $secCodigo . '_' . ($proCodigo ?? '0') . '_' . $this->academicConnection();

        return Cache::remember($cacheKey, 3600, function () use ($lapCodigo, $secCodigo, $proCodigo) {
            try {
                $conn = $this->academicConnection();
            $query = DB::connection($conn)
                ->table('seccion as sec')
                ->join('lapso_academico as lap', 'lap.lap_codigo', '=', 'sec.sec_cod_lapso_academico')
                ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
                ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
                ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
                ->where('lap.lap_codigo', $lapCodigo)
                ->where('sec.sec_codigo', $secCodigo)
                ->select([
                    'lap.lap_nombre', 'sec.sec_nombre',
                    'pro.pro_siglas', 'pro.pro_nombre',
                    'tra.tra_codigo', 'tra.tra_nombre as trayecto_nombre',
                ]);

            if ($proCodigo) {
                $query->where('pro.pro_codigo', $proCodigo);
            }

            $row = $query->first();

            if (! $row && $proCodigo) {
                $row = DB::connection($conn)
                    ->table('seccion as sec')
                    ->join('lapso_academico as lap', 'lap.lap_codigo', '=', 'sec.sec_cod_lapso_academico')
                    ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
                    ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
                    ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
                    ->where('lap.lap_codigo', $lapCodigo)
                    ->where('sec.sec_codigo', $secCodigo)
                    ->select([
                        'lap.lap_nombre', 'sec.sec_nombre',
                        'pro.pro_siglas', 'pro.pro_nombre',
                        'tra.tra_codigo', 'tra.tra_nombre as trayecto_nombre',
                    ])
                    ->first();
            }

            if (! $row) {
                return ['lap_nombre' => '', 'sec_nombre' => '', 'pro_siglas' => '', 'pro_nombre' => '', 'tra_codigo' => null, 'trayecto_nombre' => ''];
            }

            return [
                'lap_nombre' => trim((string) ($row->lap_nombre ?? '')),
                'sec_nombre' => trim((string) ($row->sec_nombre ?? '')),
                'pro_siglas' => trim((string) ($row->pro_siglas ?? '')),
                'pro_nombre' => trim((string) ($row->pro_nombre ?? '')),
                'tra_codigo' => $row->tra_codigo ?? null,
                'trayecto_nombre' => trim((string) ($row->trayecto_nombre ?? '')),
            ];
        } catch (\Throwable) {
            return ['lap_nombre' => '', 'sec_nombre' => '', 'pro_siglas' => '', 'pro_nombre' => '', 'tra_codigo' => null, 'trayecto_nombre' => ''];
        }
        });
    }

    protected function mapearEquipoGrupoRegistrado(object $grupo, ?string $cedulaEstudiante = null): object
    {
        $resumen = app(GrupoProyectoService::class);
        $secNombre = trim((string) ($grupo->sec_nombre ?? ''));
        $proSiglas = trim((string) ($grupo->pro_siglas ?? ''));
        $lapNombre = trim((string) ($grupo->lap_nombre ?? ''));

        if ($secNombre === '' && $grupo->lap_codigo > 0 && $grupo->sec_codigo > 0) {
            $etiq = $this->etiquetasContexto((int) $grupo->lap_codigo, (int) $grupo->sec_codigo, $grupo->pro_codigo);
            $secNombre = $etiq['sec_nombre'] ?: 'Sección '.$grupo->sec_codigo;
            $proSiglas = $etiq['pro_siglas'];
            $lapNombre = $etiq['lap_nombre'];
        }

        return (object) [
            'id' => $grupo->clave,
            'clave' => $grupo->clave,
            'nombre' => $grupo->nombre,
            'sec_codigo' => $grupo->sec_codigo,
            'sec_nombre' => $secNombre,
            'lap_codigo' => $grupo->lap_codigo,
            'lapso_nombre' => $lapNombre,
            'programa_siglas' => $proSiglas,
            'trayecto_nombre' => '',
            'integrantes' => $grupo->integrantes,
            'validado' => $cedulaEstudiante
                ? $resumen->estudianteEnGrupo($cedulaEstudiante, $grupo->clave)
                : true,
            'es_grupo_registrado' => true,
        ];
    }

    protected function mapearEquipo(object $row, ?string $cedulaEstudiante = null): object
    {
        $lap = (int) $row->lap_codigo;
        $sec = (int) $row->sec_codigo;
        $clave = $this->construirClave($lap, $sec);

        return (object) [
            'id' => $clave,
            'clave' => $clave,
            'nombre' => 'Sección '.trim($row->sec_nombre),
            'sec_codigo' => $sec,
            'sec_nombre' => trim($row->sec_nombre),
            'lap_codigo' => $lap,
            'lapso_nombre' => trim($row->lap_nombre ?? ''),
            'pro_codigo' => (int) ($row->pro_codigo ?? 0),
            'programa_siglas' => trim($row->pro_siglas ?? ''),
            'trayecto_nombre' => trim($row->trayecto_nombre ?? ''),
            'integrantes' => (int) ($row->integrantes ?? 0),
            'validado' => $cedulaEstudiante
                ? $this->estudiantePerteneceEquipo($cedulaEstudiante, $clave)
                : true,
        ];
    }

    protected function tablaGrupoProyectoExisteInternal(): bool
    {
        try {
            return Schema::connection($this->academicConnection())->hasTable('grupo_proyecto_estudiante');
        }
        catch (\Throwable) {
            return false;
        }
    }

    protected function perteneceGrupoProyecto(string $cedula, array $partes, int $rolId): bool
    {
        try {
            return DB::connection($this->academicConnection())
                ->table('grupo_proyecto_estudiante')
                ->whereRaw('TRIM(gpe_ced_estudiante) = ?', [$cedula])
                ->where('gpe_rol_id', $rolId)
                ->where('gpe_cod_seccion', $partes['sec_codigo'])
                ->exists();
        }
        catch (\Throwable) {
            try {
                return $this->baseInscripcionQuery()
                    ->whereRaw('TRIM(ins.ins_cedula) = ?', [$cedula])
                    ->where('lap.lap_codigo', $partes['lap_codigo'])
                    ->where('sec.sec_codigo', $partes['sec_codigo'])
                    ->exists();
            } catch (\Throwable) {
                return false;
            }
        }
    }

    /**
     * @param  array{lap_codigo: int, sec_codigo: int}  $partes
     * @return array<int, string>
     */
    protected function cedulasLideresEnSeccion(array $partes): array
    {
        if ($this->tablaGrupoProyectoExisteInternal()) {
            try {
                return DB::connection($this->academicConnection())
                    ->table('grupo_proyecto_estudiante')
                    ->where('gpe_cod_seccion', $partes['sec_codigo'])
                    ->where('gpe_rol_id', self::ROL_LIDER)
                    ->pluck(DB::raw('TRIM(gpe_ced_estudiante)'))
                    ->map(fn ($c) => trim((string) $c))
                    ->all();
            }
            catch (\Throwable) {
                //
            }
        }

        return [];
    }
}
