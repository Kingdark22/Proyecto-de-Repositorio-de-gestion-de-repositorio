<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('dual_database.repositorio_connection', 'pgsql');

        Schema::connection($connection)->create('comentarios_proyecto', function (Blueprint $table) {
            $table->bigIncrements('cop_codigo');
            $table->text('cop_descripcion');
            $table->bigInteger('pry_codigo');
            $table->bigInteger('uex_codigo')->nullable();
            $table->string('cop_nombre_contacto')->nullable();
            $table->timestamp('cop_fecha_creacion')->nullable();

            $table->index('pry_codigo', 'idx_comentarios_pry_codigo');
        });
    }

    public function down(): void
    {
        $connection = config('dual_database.repositorio_connection', 'pgsql');
        Schema::connection($connection)->dropIfExists('comentarios_proyecto');
    }
};
