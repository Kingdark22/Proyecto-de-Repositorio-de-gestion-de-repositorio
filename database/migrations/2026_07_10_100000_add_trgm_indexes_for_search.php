<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected string $connection;

    public function __construct()
    {
        $this->connection = (string) config('dual_database.repositorio_connection', 'pgsql');
    }

    public function up(): void
    {
        // Ensure pg_trgm extension is available
        try {
            DB::connection($this->connection)->statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear extensión pg_trgm: ' . $e->getMessage());
        }

        // Trigram index for fast ILIKE on pry_resumen (used in autocomplete and export search)
        try {
            DB::connection($this->connection)->statement(
                'CREATE INDEX IF NOT EXISTS idx_proyectos_resumen_trgm ON proyectos USING GIN (pry_resumen gin_trgm_ops)'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear idx_proyectos_resumen_trgm: ' . $e->getMessage());
        }

        // Trigram index for fast ILIKE on pry_direccion_logica (equipo_ref search)
        try {
            DB::connection($this->connection)->statement(
                'CREATE INDEX IF NOT EXISTS idx_proyectos_direccion_trgm ON proyectos USING GIN (pry_direccion_logica gin_trgm_ops)'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear idx_proyectos_direccion_trgm: ' . $e->getMessage());
        }

        // Nota: ya existe un índice parcial idx_proyectos_activos_estado
        // que cubre WHERE estado_logico = true AND estado_validacion = 'aprobado'
    }

    public function down(): void
    {
        try {
            DB::connection($this->connection)->statement('DROP INDEX IF EXISTS idx_proyectos_resumen_trgm');
        } catch (\Throwable) {}

        try {
            DB::connection($this->connection)->statement('DROP INDEX IF EXISTS idx_proyectos_direccion_trgm');
        } catch (\Throwable) {}

        // El índice parcial idx_proyectos_activos_estado se mantiene
    }
};
