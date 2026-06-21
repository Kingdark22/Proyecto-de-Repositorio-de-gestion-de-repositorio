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
            if (!Schema::connection($conn)->hasColumn('componente_programa', 'cantidad')) {
                $table->integer('cantidad')->default(1)->nullable()->after('tra_codigo');
            }
        });
    }

    public function down(): void
    {
        $conn = $this->conn();

        Schema::connection($conn)->table('componente_programa', function (Blueprint $table) use ($conn) {
            if (Schema::connection($conn)->hasColumn('componente_programa', 'cantidad')) {
                $table->dropColumn('cantidad');
            }
        });
    }
};
