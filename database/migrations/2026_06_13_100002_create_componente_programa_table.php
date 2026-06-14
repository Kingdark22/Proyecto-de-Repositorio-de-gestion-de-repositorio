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
        Schema::connection($this->conn())->create('componente_programa', function (Blueprint $table) {
            $table->bigIncrements('cpp_codigo');
            $table->unsignedBigInteger('comp_codigo');
            $table->integer('pro_codigo');
            $table->timestamps();

            $table->foreign('comp_codigo')
                ->references('comp_codigo')
                ->on('componentes')
                ->onDelete('cascade');

            $table->unique(['comp_codigo', 'pro_codigo']);
            $table->index('pro_codigo');
        });
    }

    public function down(): void
    {
        Schema::connection($this->conn())->dropIfExists('componente_programa');
    }
};
