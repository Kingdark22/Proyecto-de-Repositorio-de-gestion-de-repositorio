<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de grupos de proyecto del módulo (MySQL repositorio).
 * Sin columnas de lapso/sección/programa (intranet): contexto en grp_contexto (JSON).
 */
return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'mysql');

        if (Schema::connection($connection)->hasTable('grupo_proyecto_modulo')) {
            return;
        }

        if (Schema::connection($connection)->hasTable('grupo_proyecto_borrador')) {
            Schema::connection($connection)->rename('grupo_proyecto_borrador', 'grupo_proyecto_modulo');

            return;
        }

        Schema::connection($connection)->create('grupo_proyecto_modulo', function (Blueprint $table) {
            $table->id('grp_codigo');
            $table->string('grp_nombre', 120);
            $table->json('grp_contexto')->nullable();
            $table->unsignedBigInteger('grp_com_codigo')->nullable();
            $table->string('grp_creador_cedula', 20)->nullable()->index();
            $table->json('grp_miembros');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'mysql');
        Schema::connection($connection)->dropIfExists('grupo_proyecto_modulo');
    }
};
