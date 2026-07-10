<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');

        // Verificar que la tabla exista y la columna NO exista antes de agregarla
        if (! Schema::connection($connection)->hasTable('grupo_proyecto_modulo')) {
            return;
        }

        if (Schema::connection($connection)->hasColumn('grupo_proyecto_modulo', 'grp_identificador')) {
            return;
        }

        Schema::connection($connection)->table('grupo_proyecto_modulo', function (Blueprint $table) {
            $table->string('grp_identificador', 120)->nullable()->after('grp_nombre');
        });

        DB::connection($connection)->statement(
            'UPDATE grupo_proyecto_modulo SET grp_identificador = grp_nombre WHERE grp_identificador IS NULL'
        );
    }

    public function down(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');

        if (Schema::connection($connection)->hasColumn('grupo_proyecto_modulo', 'grp_identificador')) {
            Schema::connection($connection)->table('grupo_proyecto_modulo', function (Blueprint $table) {
                $table->dropColumn('grp_identificador');
            });
        }
    }
};
