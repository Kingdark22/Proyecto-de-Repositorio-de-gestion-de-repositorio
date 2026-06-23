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

        if (Schema::connection($connection)->hasTable('roles_involucrados')) {
            return;
        }

        Schema::connection($connection)->create('roles_involucrados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->timestamps();
        });

        // Insertar roles comunes por defecto
        $roles = ['Tutor', 'Coordinador', 'Asesor', 'Representante', 'Promotor', 'Facilitador', 'Evaluador'];
        try {
            DB::connection($connection)->table('roles_involucrados')->insert(
                array_map(fn($r) => ['nombre' => $r, 'created_at' => now(), 'updated_at' => now()], $roles)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudieron insertar roles por defecto: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');
        Schema::connection($connection)->dropIfExists('roles_involucrados');
    }
};
