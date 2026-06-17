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
        Schema::connection($this->conn())->table('componentes', function (Blueprint $table) {
            if (!Schema::connection($this->conn())->hasColumn('componentes', 'comp_tipo_archivo')) {
                $table->string('comp_tipo_archivo', 100)->nullable()->after('comp_estado_logico');
            }
            if (!Schema::connection($this->conn())->hasColumn('componentes', 'comp_tamano_maximo_mb')) {
                $table->integer('comp_tamano_maximo_mb')->nullable()->after('comp_tipo_archivo');
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->conn())->table('componentes', function (Blueprint $table) {
            $table->dropColumn(['comp_tipo_archivo', 'comp_tamano_maximo_mb']);
        });
    }
};
