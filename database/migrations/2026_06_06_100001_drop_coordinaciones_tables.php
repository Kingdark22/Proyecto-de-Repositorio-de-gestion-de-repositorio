<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('dual_database.repositorio_connection', 'mysql');

        if (Schema::connection($connection)->hasColumn('profesor_proyecto_modulo', 'ppm_coordinacion_id')) {
            Schema::connection($connection)->table('profesor_proyecto_modulo', function ($table) {
                $table->dropColumn('ppm_coordinacion_id');
            });
        }

        if (Schema::connection($connection)->hasTable('coordinaciones')) {
            Schema::connection($connection)->dropIfExists('coordinaciones');
        }
    }

    public function down(): void
    {
    }
};
