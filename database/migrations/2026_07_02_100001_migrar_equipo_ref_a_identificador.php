<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');

        if (! Schema::connection($connection)->hasTable('grupo_proyecto_modulo') || ! Schema::connection($connection)->hasTable('proyectos')) {
            return;
        }

        if (! Schema::connection($connection)->hasColumn('grupo_proyecto_modulo', 'grp_identificador')) {
            return;
        }

        $updated = DB::connection($connection)->statement("
            UPDATE proyectos p
            SET pry_direccion_logica = g.grp_identificador
            FROM grupo_proyecto_modulo g
            WHERE p.pry_direccion_logica = 'EQGRP:' || g.grp_codigo
              AND g.grp_identificador IS NOT NULL
              AND g.grp_identificador != ''
        ");

        if ($updated > 0) {
            \Illuminate\Support\Facades\Log::info("Migrados {$updated} proyectos de EQGRP a identificador.");
        }
    }

    public function down(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');

        if (! Schema::connection($connection)->hasColumn('grupo_proyecto_modulo', 'grp_identificador')) {
            return;
        }

        DB::connection($connection)->statement("
            UPDATE proyectos p
            SET pry_direccion_logica = 'EQGRP:' || g.grp_codigo
            FROM grupo_proyecto_modulo g
            WHERE p.pry_direccion_logica = g.grp_identificador
        ");
    }
};
