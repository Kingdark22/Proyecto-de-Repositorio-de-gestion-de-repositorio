<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');

        if (!Schema::connection($connection)->hasColumn('grupo_proyecto_modulo', 'estado_logico')) {
            Schema::connection($connection)->table('grupo_proyecto_modulo', function (Blueprint $table) {
                $table->boolean('estado_logico')->default(true)->after('grp_miembros');
            });
        }
    }

    public function down(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');

        if (Schema::connection($connection)->hasColumn('grupo_proyecto_modulo', 'estado_logico')) {
            Schema::connection($connection)->table('grupo_proyecto_modulo', function (Blueprint $table) {
                $table->dropColumn('estado_logico');
            });
        }
    }
};
