<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('dual_database.repositorio_connection', 'mysql');

        Schema::connection($connection)->table('proyectos', function ($table) {
            $table->index('pry_estado_validacion', 'idx_proyectos_validacion');
        });

        try {
            DB::connection($connection)->statement('ALTER TABLE proyectos ADD INDEX idx_proyectos_validacion_dir (pry_estado_validacion, pry_direccion_logica)');
        } catch (\Throwable) {
        }

        Schema::connection($connection)->table('proyectos_publicados', function ($table) {
            $table->index('pub_estado', 'idx_publicados_estado');
            $table->index('pry_codigo', 'idx_publicados_pry_codigo');
        });

        Schema::connection($connection)->table('comentarios_proyecto', function ($table) {
            $table->index('pry_codigo', 'idx_comentarios_pry_codigo');
        });
    }

    public function down(): void
    {
        $connection = config('dual_database.repositorio_connection', 'mysql');

        Schema::connection($connection)->table('proyectos', function ($table) {
            $table->dropIndex('idx_proyectos_validacion');
        });

        try {
            DB::connection($connection)->statement('ALTER TABLE proyectos DROP INDEX idx_proyectos_validacion_dir');
        } catch (\Throwable) {
        }

        Schema::connection($connection)->table('proyectos_publicados', function ($table) {
            $table->dropIndex('idx_publicados_estado');
            $table->dropIndex('idx_publicados_pry_codigo');
        });

        Schema::connection($connection)->table('comentarios_proyecto', function ($table) {
            $table->dropIndex('idx_comentarios_pry_codigo');
        });
    }
};
