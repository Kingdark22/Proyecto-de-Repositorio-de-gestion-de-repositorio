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
            $table->index('pry_direccion_logica', 'idx_proyectos_direccion_logica');
        });

        try {
            DB::connection($connection)->statement('ALTER TABLE proyectos ADD FULLTEXT INDEX ft_proyectos_resumen (pry_resumen)');
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        $connection = config('dual_database.repositorio_connection', 'mysql');

        Schema::connection($connection)->table('proyectos', function ($table) {
            $table->dropIndex('idx_proyectos_direccion_logica');
        });

        try {
            DB::connection($connection)->statement('ALTER TABLE proyectos DROP INDEX ft_proyectos_resumen');
        } catch (\Throwable $e) {
        }
    }
};
