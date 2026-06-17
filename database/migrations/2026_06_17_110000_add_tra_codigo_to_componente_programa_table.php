<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function conn(): string
    {
        return (string) config('dual_database.repositorio_connection', 'pgsql');
    }

    public function up(): void
    {
        $conn = $this->conn();

        Schema::connection($conn)->table('componente_programa', function (Blueprint $table) use ($conn) {
            if (!Schema::connection($conn)->hasColumn('componente_programa', 'tra_codigo')) {
                $table->string('tra_codigo', 10)->nullable()->after('pro_codigo');
            }
        });

        // Drop existing unique constraint, then add new one with tra_codigo
        try {
            Schema::connection($conn)->table('componente_programa', function (Blueprint $table) {
                $table->dropUnique(['comp_codigo', 'pro_codigo']);
            });
        } catch (\Throwable) {
            // Might not exist or have a different name - try alternate name
            try {
                Schema::connection($conn)->table('componente_programa', function (Blueprint $table) {
                    $table->dropUnique('componente_programa_comp_codigo_pro_codigo_unique');
                });
            } catch (\Throwable) {
                // Already dropped or different name - continue
            }
        }

        Schema::connection($conn)->table('componente_programa', function (Blueprint $table) {
            $table->unique(['comp_codigo', 'pro_codigo', 'tra_codigo'], 'comp_prog_tra_unique');
        });
    }

    public function down(): void
    {
        $conn = $this->conn();

        Schema::connection($conn)->table('componente_programa', function (Blueprint $table) use ($conn) {
            if (Schema::connection($conn)->hasColumn('componente_programa', 'tra_codigo')) {
                $table->dropColumn('tra_codigo');
            }
        });

        try {
            Schema::connection($conn)->table('componente_programa', function (Blueprint $table) {
                $table->dropUnique('comp_prog_tra_unique');
            });
        } catch (\Throwable) {
            // ignore
        }

        Schema::connection($conn)->table('componente_programa', function (Blueprint $table) {
            $table->unique(['comp_codigo', 'pro_codigo']);
        });
    }
};
