<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection;

    public function __construct()
    {
        $this->connection = (string) config('dual_database.repositorio_connection', 'pgsql');
    }

    public function up(): void
    {
        // Trgm en grp_nombre para búsquedas por nombre de grupo
        try {
            DB::connection($this->connection)->statement(
                'CREATE INDEX IF NOT EXISTS idx_gpm_nombre_trgm ON grupo_proyecto_modulo USING GIN (grp_nombre gin_trgm_ops)'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear idx_gpm_nombre_trgm: ' . $e->getMessage());
        }

        // Índice btree en grp_identificador para EXISTS rápido
        try {
            DB::connection($this->connection)->statement(
                'CREATE INDEX IF NOT EXISTS idx_gpm_identificador ON grupo_proyecto_modulo (grp_identificador) WHERE grp_identificador IS NOT NULL'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear idx_gpm_identificador: ' . $e->getMessage());
        }

        // Índice btree en pry_direccion_logica para EXISTS rápido
        try {
            DB::connection($this->connection)->statement(
                'CREATE INDEX IF NOT EXISTS idx_proyectos_direccion_logica ON proyectos (pry_direccion_logica) WHERE pry_direccion_logica IS NOT NULL'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear idx_proyectos_direccion_logica: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        try { DB::connection($this->connection)->statement('DROP INDEX IF EXISTS idx_gpm_nombre_trgm'); } catch (\Throwable) {}
        try { DB::connection($this->connection)->statement('DROP INDEX IF EXISTS idx_gpm_identificador'); } catch (\Throwable) {}
        try { DB::connection($this->connection)->statement('DROP INDEX IF EXISTS idx_proyectos_direccion_logica'); } catch (\Throwable) {}
    }
};
