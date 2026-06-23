<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');

        if (Schema::connection($connection)->hasTable('proyecto_involucrado')) {
            return;
        }

        Schema::connection($connection)->create('proyecto_involucrado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proyecto_id');
            $table->unsignedBigInteger('involucrado_id');
            $table->timestamps();

            $table->unique(['proyecto_id', 'involucrado_id']);
            $table->index('proyecto_id');
            $table->index('involucrado_id');
        });
    }

    public function down(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');
        Schema::connection($connection)->dropIfExists('proyecto_involucrado');
    }
};
