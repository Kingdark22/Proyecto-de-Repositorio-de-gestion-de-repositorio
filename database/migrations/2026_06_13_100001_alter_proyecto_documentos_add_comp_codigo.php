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
        Schema::connection($this->conn())->table('proyecto_documentos', function (Blueprint $table) {
            $table->dropColumn('pd_nombre');
        });

        Schema::connection($this->conn())->table('proyecto_documentos', function (Blueprint $table) {
            $table->unsignedBigInteger('comp_codigo');
            $table->foreign('comp_codigo')
                ->references('comp_codigo')
                ->on('componentes')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::connection($this->conn())->table('proyecto_documentos', function (Blueprint $table) {
            $table->dropForeign(['comp_codigo']);
            $table->dropColumn('comp_codigo');
        });

        Schema::connection($this->conn())->table('proyecto_documentos', function (Blueprint $table) {
            $table->string('pd_nombre', 255);
        });
    }
};
