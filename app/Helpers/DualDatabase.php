<?php

namespace App\Helpers;

use App\Services\IntranetSimulationMirrorService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DualDatabase
{
    public static function academicConnection(): string
    {
        return DbHelper::connection();
    }

    public static function repositorioConnection(): string
    {
        return (string) config('dual_database.repositorio_connection', 'mysql');
    }

    public static function isIntranetTable(string $table): bool
    {
        return in_array($table, config('dual_database.intranet_tables', []), true);
    }

    public static function isRepositorioTable(string $table): bool
    {
        return in_array($table, config('dual_database.repositorio_tables', []), true);
    }

    public static function hasTable(string $connection, string $table): bool
    {
        try {
            return Schema::connection($connection)->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Lectura académica: solo intranet o simulación (sin copiar a repositorio).
     *
     * @deprecated Use tablaAcademicaSoloLectura() o ConexionDualService::consultaAcademica()
     */
    public static function table(string $table): Builder
    {
        if (self::isIntranetTable($table)) {
            return self::tablaAcademicaSoloLectura($table);
        }

        return self::repositorioTable($table);
    }

    /**
     * SELECT sobre intranet/simulación. No insertar ni actualizar aquí.
     */
    public static function tablaAcademicaSoloLectura(string $table): Builder
    {
        if (! self::isIntranetTable($table)) {
            throw new RuntimeException("[{$table}] no está catalogada como tabla académica (solo lectura).");
        }

        $academic = self::academicConnection();

        if (! self::hasTable($academic, $table)) {
            throw new RuntimeException("La tabla académica [{$table}] no existe en {$academic}.");
        }

        return DB::connection($academic)->table($table);
    }

    /**
     * Tablas propias del módulo repositorio (siempre MySQL repositorio).
     */
    public static function repositorioTable(string $table): Builder
    {
        $repo = self::repositorioConnection();

        if (! self::hasTable($repo, $table)) {
            throw new RuntimeException("La tabla [{$table}] no existe en la base repositorio.");
        }

        return DB::connection($repo)->table($table);
    }

    /**
     * @return array<int, object>
     */
    public static function get(string $table, ?string $orderBy = null): array
    {
        $query = self::table($table);
        if ($orderBy) {
            $query->orderBy($orderBy);
        }

        $rows = $query->get()->all();
        self::mirrorAcademicRows($table, $rows);

        return $rows;
    }

    /**
     * @return object|null
     */
    public static function firstWhere(string $table, string $column, mixed $value): ?object
    {
        $row = self::table($table)->where($column, $value)->first();
        if ($row !== null) {
            self::mirrorAcademicRows($table, [$row]);
        }

        return $row;
    }

    /**
     * @param  iterable<int, object|array<string, mixed>>  $rows
     */
    public static function mirrorAcademicRows(string $table, iterable $rows): void
    {
        if (! self::isIntranetTable($table)) {
            return;
        }

        app(IntranetSimulationMirrorService::class)->mirrorRows($table, $rows);
    }
}
