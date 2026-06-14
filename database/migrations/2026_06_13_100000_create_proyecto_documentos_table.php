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
        Schema::connection($this->conn())->create('proyecto_documentos', function (Blueprint $table) {
            $table->bigIncrements('pd_codigo');
            $table->unsignedBigInteger('pry_codigo');
            $table->string('pd_nombre', 255);
            $table->string('pd_archivo_path', 500);
            $table->integer('pd_orden')->default(0);
            $table->timestamps();

            $table->foreign('pry_codigo')
                ->references('pry_codigo')
                ->on('proyectos')
                ->onDelete('cascade');

            $table->index('pry_codigo');
        });
    }

    public function down(): void
    {
        Schema::connection($this->conn())->dropIfExists('proyecto_documentos');
    }
};
