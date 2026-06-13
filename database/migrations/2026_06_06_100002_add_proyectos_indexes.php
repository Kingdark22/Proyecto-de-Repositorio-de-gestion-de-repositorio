<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('dual_database.repositorio_connection', 'pgsql');

        Schema::connection($connection)->table('proyectos', function ($table) {
            $table->index('pry_direccion_logica', 'idx_proyectos_direccion_logica');
        });

        try {
            DB::connection($connection)->statement('CREATE INDEX ft_proyectos_resumen ON proyectos USING GIN (to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')))');
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        $connection = config('dual_database.repositorio_connection', 'pgsql');

        Schema::connection($connection)->table('proyectos', function ($table) {
            $table->dropIndex('idx_proyectos_direccion_logica');
        });

        try {
            DB::connection($connection)->statement('DROP INDEX IF EXISTS ft_proyectos_resumen');
        } catch (\Throwable $e) {
        }
    }
};
