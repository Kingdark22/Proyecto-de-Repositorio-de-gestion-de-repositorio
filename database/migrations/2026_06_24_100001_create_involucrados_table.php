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

        if (Schema::connection($connection)->hasTable('involucrados')) {
            return;
        }

        Schema::connection($connection)->create('involucrados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('apellido', 150);
            $table->string('cedula', 20)->unique();
            $table->timestamps();
        });

        try {
            DB::connection($connection)->statement(
                'CREATE INDEX IF NOT EXISTS idx_involucrados_cedula ON involucrados (cedula)'
            );
            DB::connection($connection)->statement(
                'CREATE INDEX IF NOT EXISTS idx_involucrados_nombre ON involucrados USING GIN (to_tsvector(\'spanish\', nombre || \' \' || apellido))'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear índice en involucrados: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'pgsql');
        Schema::connection($connection)->dropIfExists('involucrados');
    }
};
