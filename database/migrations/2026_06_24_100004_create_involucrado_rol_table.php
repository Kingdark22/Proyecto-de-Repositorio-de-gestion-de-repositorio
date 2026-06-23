<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');

        if (Schema::connection($connection)->hasTable('involucrado_rol')) {
            return;
        }

        Schema::connection($connection)->create('involucrado_rol', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proyecto_involucrado_id');
            $table->unsignedBigInteger('rol_id');
            $table->timestamps();

            $table->unique(['proyecto_involucrado_id', 'rol_id'], 'involucrado_rol_unique');
            $table->index('proyecto_involucrado_id');
            $table->index('rol_id');
        });
    }

    public function down(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');
        Schema::connection($connection)->dropIfExists('involucrado_rol');
    }
};
