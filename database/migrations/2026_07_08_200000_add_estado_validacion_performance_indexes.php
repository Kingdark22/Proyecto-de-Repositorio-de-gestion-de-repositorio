<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('dual_database.repositorio_connection', 'pgsql');

        // Índice compuesto para filtros de notificaciones y listado
        // Cubre: WHERE estado_validacion = 'completado'
        //        WHERE estado_validacion = 'pendiente' AND actualizado_por_estudiante = true
        //        WHERE estado_validacion IN (...)
        try {
            DB::connection($connection)->statement(
                "CREATE INDEX IF NOT EXISTS idx_proyectos_estado_val_estudiante
                 ON proyectos (estado_validacion, actualizado_por_estudiante)"
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear idx_proyectos_estado_val_estudiante: ' . $e->getMessage());
        }

        // Índice parcial para proyectos activos (estado_logico = true)
        // Cubre: WHERE estado_logico = true AND estado_validacion = 'completado'
        try {
            DB::connection($connection)->statement(
                "CREATE INDEX IF NOT EXISTS idx_proyectos_activos_estado
                 ON proyectos (estado_validacion)
                 WHERE estado_logico = true"
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear idx_proyectos_activos_estado: ' . $e->getMessage());
        }

        // Índice en proyecto_documentos para la consulta de rechazados
        // Cubre: WHERE pd_estado = 2 (documentos rechazados)
        try {
            DB::connection($connection)->statement(
                "CREATE INDEX IF NOT EXISTS idx_proyecto_docs_estado
                 ON proyecto_documentos (pd_estado, pry_codigo)"
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear idx_proyecto_docs_estado: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        $connection = config('dual_database.repositorio_connection', 'pgsql');

        try {
            DB::connection($connection)->statement('DROP INDEX IF EXISTS idx_proyectos_estado_val_estudiante');
        } catch (\Throwable) {}

        try {
            DB::connection($connection)->statement('DROP INDEX IF EXISTS idx_proyectos_activos_estado');
        } catch (\Throwable) {}

        try {
            DB::connection($connection)->statement('DROP INDEX IF EXISTS idx_proyecto_docs_estado');
        } catch (\Throwable) {}
    }
};
