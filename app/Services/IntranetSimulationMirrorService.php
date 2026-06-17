<?php

namespace App\Services;

use App\Helpers\DbHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Persona;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\SeccionUnidadDocente;
use App\Models\UnidadCurricular;
use App\Models\Seccion;
use App\Models\LapsoAcademico;
use App\Models\Malla;
use App\Models\Programa;
use App\Models\Rol;
use App\Models\Trayecto;

/**
 * Espeja filas leídas desde intranet hacia la BD MySQL de simulación (fallback).
 * Solo intranet → simulación. Nunca escribe en intranet ni en repositorio.
 * No crea tablas: solo INSERT/UPDATE en tablas que ya existen en simulación.
 */
class IntranetSimulationMirrorService
{
    protected static array $mirroredUserContexts = [];

    protected static array $mirroredCatalogs = [];

    public function enabled(): bool
    {
        return (bool) config('intranet_mirror.enabled', true);
    }

    public function simulationConnection(): string
    {
        return (string) config('intranet_mirror.simulation_connection', 'simulacion');
    }

    public function shouldMirrorFromIntranet(): bool
    {
        return $this->enabled()
            && DbHelper::connection() === 'intranet'
            && DbHelper::isUsingIntranet();
    }

    /**
     * @param  iterable<int, object|array<string, mixed>>  $rows
     */
    public function mirrorRows(string $table, iterable $rows): int
    {
        if (! $this->shouldMirrorFromIntranet()) {
            return 0;
        }

        if (! $this->isMirrorableTable($table)) {
            return 0;
        }

        if (! $this->simulationHasTable($table)) {
            return 0;
        }

        $sim = $this->simulationConnection();
        $pk = $this->primaryKey($table);
        $written = 0;

        // Convertir a array para contar
        if ($rows instanceof \Illuminate\Support\Collection) {
            $rowsArray = $rows->all();
        } else if ($rows instanceof \Traversable) {
            $rowsArray = iterator_to_array($rows);
        } else {
            $rowsArray = (array)$rows;
        }

        if (empty($rowsArray)) return 0;

        foreach ($rowsArray as $row) {
            $rowData = $row instanceof Model ? $row->getAttributes() : (array) $row;
            $payload = $this->filterRowForConnection($sim, $table, $rowData);
            if ($payload === [] || ($pk && ! array_key_exists($pk, $payload))) {
                continue;
            }

            try {
                // Optimización: Solo actualizar si es necesario o usar insert ignore/on duplicate si fuera MySQL a MySQL
                // Pero como es un espejo de respaldo, updateOrInsert es lo más seguro.
                $modelClass = $this->getModelForTable($table);
                if ($modelClass) {
                    if ($pk) {
                        $modelClass::on($sim)->updateOrInsert(
                            [$pk => $payload[$pk]],
                            $payload
                        );
                    } else {
                        $modelClass::on($sim)->insert($payload);
                    }
                } else {
                    // Fallback to DB::table if no model is found
                    if ($pk) {
                        DB::connection($sim)->table($table)->updateOrInsert(
                            [$pk => $payload[$pk]],
                            $payload
                        );
                    } else {
                        DB::connection($sim)->table($table)->insert($payload);
                    }
                }
                $written++;
            } catch (\Throwable $e) {
                Log::warning("Espejo intranet→simulación falló en {$table}: {$e->getMessage()}");
            }
        }

        return $written;
    }

    /**
     * Actualiza el rol de un usuario específicamente en la base de datos de simulación.
     */
    public function updateSimulationUserRole(string $cedula, string $roleSlug): void
    {
        if (! $this->simulationHasTable('usuario')) {
            return;
        }

        $sim = $this->simulationConnection();
        $cedula = trim($cedula);
        
        // Mapeo inverso slug -> código (basado en config/roles.php)
        $map = array_flip(config('roles.usu_cod_rol_map', []));
        $codRol = $map[$roleSlug] ?? null;

        if ($codRol !== null) {
            try {
                User::on($sim)
                    ->where('usu_cedula', $cedula)
                    ->update(['usu_cod_rol' => $codRol]);
            } catch (\Throwable $e) {
                Log::warning("No se pudo actualizar rol en simulación para {$cedula}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Contexto académico de un usuario (login / detectar roles).
     */
    public function mirrorUserContext(string $cedula): int
    {
        if (! $this->shouldMirrorFromIntranet()) {
            return 0;
        }

        $cedula = trim($cedula);
        if ($cedula === '') {
            return 0;
        }

        if (isset(self::$mirroredUserContexts[$cedula])) {
            return 0;
        }
        self::$mirroredUserContexts[$cedula] = true;

        $intranet = 'intranet';
        $total = 0;

        // ── FK ORDER (simulation DB) ──
        // 1. persona, programa, trayecto, lapso_academico (sin FK)
        // 2. malla            → programa, trayecto
        // 3. unidad_curricular → malla
        // 4. seccion           → lapso_academico, malla
        // 5. estudiante        → persona, programa
        // 6. usuario           → persona
        // 7. inscripcion       → estudiante
        // 8. seccion_unidad_docente (no tiene FK en simulación)
        // ─────────────────────────────

        // 1. CATÁLOGOS BASE (sin FK) — deben ir ANTES que estudiante
        if (! isset(self::$mirroredCatalogs['programa']) && $this->simulationHasTable('programa')) {
            self::$mirroredCatalogs['programa'] = true;
            $total += $this->mirrorAllPrograms();
        }
        if (! isset(self::$mirroredCatalogs['trayecto']) && $this->simulationHasTable('trayecto')) {
            self::$mirroredCatalogs['trayecto'] = true;
            try {
                if (!ini_get('safe_mode')) set_time_limit(120);
                $total += $this->mirrorTable('trayecto');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Espejo trayecto falló: ' . $e->getMessage());
            }
        }
        if (! isset(self::$mirroredCatalogs['lapso_academico']) && $this->simulationHasTable('lapso_academico')) {
            self::$mirroredCatalogs['lapso_academico'] = true;
            $total += $this->mirrorActiveLapsos();
        }

        // 2. Persona (sin FK, referenciada por usuario y estudiante)
        $persona = Persona::on($intranet)
            ->where('per_cedula', $cedula)
            ->first();
        if ($persona) {
            $total += $this->mirrorRows('persona', [$persona]);
        }

        // 3-4. Malla y unidad_curricular se espejan dentro de mirrorInscripcionesForStudent
        //       y mirrorDocenteAssignments, en el orden correcto (seccion después de malla/lapso).

        // 5-6. Estudiante (needs persona, programa) y Usuario (needs persona)
        $usuario = User::on($intranet)
            ->where('usu_cedula', $cedula)
            ->first();
        if ($usuario) {
            $total += $this->mirrorRows('usuario', [$usuario]);
        }

        $estudiante = Estudiante::on($intranet)
            ->where('est_cedula', $cedula)
            ->first();
        if ($estudiante) {
            // 5. estudiante (needs persona, programa — ya espejados arriba)
            $total += $this->mirrorRows('estudiante', [$estudiante]);
            // 7. inscripcion (needs estudiante) + sus dependencias
            $total += $this->mirrorInscripcionesForStudent($cedula);
        }

        // 8. Docente assignments
        if ($this->simulationHasTable('seccion_unidad_docente')) {
            $total += $this->mirrorDocenteAssignments($cedula);
        }

        // Rol (catálogo, sin FK)
        if (! isset(self::$mirroredCatalogs['rol']) && $this->simulationHasTable('rol')) {
            self::$mirroredCatalogs['rol'] = true;
            $total += $this->mirrorTable('rol');
        }

        return $total;
    }

    /**
     * Espeja todos los programas (PNFs) activos desde intranet.
     */
    public function mirrorAllPrograms(): int
    {
        return $this->mirrorTable('programa');
    }

    /**
     * Espeja una tabla completa desde intranet a simulación con optimización de tiempo.
     */
    public function mirrorTable(string $table): int
    {
        if (! $this->shouldMirrorFromIntranet() || ! $this->simulationHasTable($table)) {
            return 0;
        }

        try {
            // Aumentar tiempo de ejecución para procesos largos de espejo
            if (!ini_get('safe_mode')) {
                set_time_limit(300); 
            }

            // Si la tabla es muy grande, podríamos paginarla, pero por ahora traemos lo básico
            $rows = DB::connection('intranet')->table($table)->get();

            return $this->mirrorRows($table, $rows);
        } catch (\Throwable $e) {
            Log::warning("Espejo de tabla {$table} falló: {$e->getMessage()}");
            return 0;
        }
    }

    /**
     * Espeja todos los catálogos académicos importantes.
     */
    public function mirrorAllCatalogs(): array
    {
        $tables = [
            'programa',
            'trayecto',
            'malla',
            'unidad_curricular',
            'lapso_academico',
            'estado',
            'municipio',
            'parroquia',
            'rol'
        ];

        $results = [];
        foreach ($tables as $table) {
            $results[$table] = $this->mirrorTable($table);
        }

        return $results;
    }

    protected function mirrorInscripcionesForStudent(string $cedula): int
    {
        if (! $this->simulationHasTable('inscripcion')) {
            return 0;
        }

        $rows = Inscripcion::on('intranet')
            ->where('ins_cedula', $cedula)
            ->get();

        $total = $this->mirrorRows('inscripcion', $rows);

        $sudIds = $rows->map(fn ($r) => $r->ins_cod_seccion_unidad_docente ?? $r->ins_cod_seccion_unidad_doc ?? null)
            ->filter()
            ->unique()
            ->values();

        if ($sudIds->isNotEmpty() && $this->simulationHasTable('seccion_unidad_docente')) {
            $suds = SeccionUnidadDocente::on('intranet')
                ->whereIn('sud_codigo', $sudIds->all())
                ->get();
            $total += $this->mirrorRows('seccion_unidad_docente', $suds);
            $total += $this->mirrorSectionsFromSud($suds);
        }

        return $total;
    }

    protected function mirrorDocenteAssignments(string $cedula): int
    {
        if (! $this->simulationHasTable('seccion_unidad_docente')) {
            return 0;
        }

        $suds = SeccionUnidadDocente::on('intranet')
            ->where('sud_ced_docente', $cedula)
            ->get();

        $total = $this->mirrorRows('seccion_unidad_docente', $suds);
        $total += $this->mirrorSectionsFromSud($suds);

        $ucuIds = $suds->pluck('sud_cod_unidad')->filter()->unique();
        if ($ucuIds->isNotEmpty()) {
            $ucus = UnidadCurricular::on('intranet')
                ->whereIn('ucu_codigo', $ucuIds->all())
                ->get();
            $total += $this->mirrorRows('unidad_curricular', $ucus);
            $total += $this->mirrorMallasFromUcu($ucus);
        }

        return $total;
    }

    /**
     * @param  Collection<int, object>  $suds
     */
    protected function mirrorSectionsFromSud(Collection $suds): int
    {
        $secIds = $suds->pluck('sud_cod_seccion')->filter()->unique();
        if ($secIds->isEmpty()) {
            return 0;
        }

        $secciones = Seccion::on('intranet')
            ->whereIn('sec_codigo', $secIds->all())
            ->get();

        $total = $this->mirrorRows('seccion', $secciones);

        $lapIds = $secciones->pluck('sec_cod_lapso_academico')->filter()->unique();
        if ($lapIds->isNotEmpty()) {
            $lapsos = LapsoAcademico::on('intranet')
                ->whereIn('lap_codigo', $lapIds->all())
                ->get();
            $total += $this->mirrorRows('lapso_academico', $lapsos);
        }

        $malIds = $secciones->pluck('sec_cod_malla')->filter()->unique();
        if ($malIds->isNotEmpty()) {
            $mallas = Malla::on('intranet')
                ->whereIn('mal_codigo', $malIds->all())
                ->get();
            $total += $this->mirrorRows('malla', $mallas);
            $total += $this->mirrorProgramasFromMalla($mallas);
        }

        return $total;
    }

    /**
     * @param  Collection<int, object>  $ucus
     */
    protected function mirrorMallasFromUcu(Collection $ucus): int
    {
        $malIds = $ucus->pluck('ucu_cod_malla')->filter()->unique();
        if ($malIds->isEmpty()) {
            return 0;
        }

        $mallas = Malla::on('intranet')
            ->whereIn('mal_codigo', $malIds->all())
            ->get();

        $total = $this->mirrorRows('malla', $mallas);

        return $total + $this->mirrorProgramasFromMalla($mallas);
    }

    /**
     * @param  Collection<int, object>  $mallas
     */
    protected function mirrorProgramasFromMalla(Collection $mallas): int
    {
        $proIds = $mallas->pluck('mal_cod_programa')->filter()->unique();
        $traIds = $mallas->pluck('mal_cod_trayecto')->filter()->unique();
        $total = 0;

        if ($proIds->isNotEmpty()) {
            $total += $this->mirrorRows(
                'programa',
                Programa::on('intranet')->whereIn('pro_codigo', $proIds->all())->get()
            );
        }

        if ($traIds->isNotEmpty() && Schema::connection('intranet')->hasTable('trayecto')) {
            $total += $this->mirrorRows(
                'trayecto',
                Trayecto::on('intranet')->whereIn('tra_codigo', $traIds->all())->get()
            );
        }

        return $total;
    }

    protected function mirrorActiveLapsos(): int
    {
        $estatus = config('proyecto_profesor.lapso_estatus_activo', 'A');
        $lapsos = LapsoAcademico::on('intranet')
            ->where('lap_estatus', $estatus)
            ->orderByDesc('lap_codigo')
            ->limit(5)
            ->get();

        return $this->mirrorRows('lapso_academico', $lapsos);
    }

    public function simulationHasTable(string $table): bool
    {
        try {
            return Schema::connection($this->simulationConnection())->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function isMirrorableTable(string $table): bool
    {
        if (! in_array($table, config('dual_database.intranet_tables', []), true)) {
            return false;
        }

        if (config('intranet_mirror.only_existing_simulation_tables', true)) {
            return $this->simulationHasTable($table);
        }

        return true;
    }

    protected function primaryKey(string $table): ?string
    {
        return config("intranet_mirror.primary_keys.{$table}");
    }

    /**
     * Mapa de conversión de valores: columna 'tabla.columna' => [valor_intranet => valor_simulacion].
     * Necesario porque la BD de simulación (SOGAC) usa tipos ENUM con valores diferentes a intranet.
     */
    protected const VALUE_MAP = [
        // persona.per_genero: intranet usa '0'/'1', simulación espera 'M'/'F'
        'persona.per_genero' => [
            '0' => 'M',
            '1' => 'F',
        ],
    ];

    /**
     * Columnas a excluir del payload al insertar en simulación.
     * Útil cuando la simulación tiene un PK auto-incremental (serial) que no debe incluirse,
     * o columnas que existen en intranet pero causan conflictos en simulación.
     */
    protected const EXCLUDED_COLUMNS = [
        // usuario: simulacion tiene usu_codigo como serial PK, intranet lo incluye como columna regular
        'usuario' => ['usu_codigo'],
    ];

    /**
     * @return array<string, mixed>
     */
    protected function filterRowForConnection(string $connection, string $table, array $row): array
    {
        $columns = Schema::connection($connection)->getColumnListing($table);
        $allowed = array_flip($columns);
        $filtered = [];

        // Columnas excluidas para esta tabla
        $excluded = self::EXCLUDED_COLUMNS[$table] ?? [];
        $excludedKeys = array_flip($excluded);

        foreach ($row as $key => $value) {
            if (isset($allowed[$key]) && ! isset($excludedKeys[$key])) {
                // Recortar strings (intranet suele tener espacios al final)
                if (is_string($value)) {
                    $value = trim($value);
                }
                // Aplicar mapa de conversión de valores si existe para esta columna
                $mapKey = $table . '.' . $key;
                if (isset(self::VALUE_MAP[$mapKey]) && $value !== null) {
                    $value = self::VALUE_MAP[$mapKey][$value] ?? $value;
                }
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    protected function getModelForTable(string $table): ?string
    {
        switch ($table) {
            case 'usuario': return User::class;
            case 'persona': return Persona::class;
            case 'estudiante': return Estudiante::class;
            case 'inscripcion': return Inscripcion::class;
            case 'seccion_unidad_docente': return SeccionUnidadDocente::class;
            case 'seccion': return Seccion::class;
            case 'lapso_academico': return LapsoAcademico::class;
            case 'malla': return Malla::class;
            case 'programa': return Programa::class;
            case 'trayecto': return Trayecto::class;
            case 'unidad_curricular': return UnidadCurricular::class;
            case 'rol': return Rol::class;
            default: return null;
        }
    }
}
