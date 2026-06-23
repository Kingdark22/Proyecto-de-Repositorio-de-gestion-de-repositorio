<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega índices GIN en grp_contexto y grp_miembros (JSONB) para acelerar
 * las consultas con operadores JSONB (->>, @>, etc.) en grupo_proyecto_modulo.
 */
return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');

        if (! Schema::connection($connection)->hasTable('grupo_proyecto_modulo')) {
            return;
        }

        try {
            DB::connection($connection)->statement(
                'CREATE INDEX IF NOT EXISTS idx_grp_contexto_gin ON grupo_proyecto_modulo USING GIN (grp_contexto)'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear índice GIN en grp_contexto: ' . $e->getMessage());
        }

        try {
            DB::connection($connection)->statement(
                'CREATE INDEX IF NOT EXISTS idx_grp_miembros_gin ON grupo_proyecto_modulo USING GIN (grp_miembros)'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear índice GIN en grp_miembros: ' . $e->getMessage());
        }

        try {
            DB::connection($connection)->statement(
                'CREATE INDEX IF NOT EXISTS idx_grp_nombre_lower ON grupo_proyecto_modulo (LOWER(grp_nombre))'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear índice funcional en grp_nombre: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');

        try {
            DB::connection($connection)->statement('DROP INDEX IF EXISTS idx_grp_contexto_gin');
            DB::connection($connection)->statement('DROP INDEX IF EXISTS idx_grp_miembros_gin');
            DB::connection($connection)->statement('DROP INDEX IF EXISTS idx_grp_nombre_lower');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error al eliminar índices GIN: ' . $e->getMessage());
        }
    }
};
