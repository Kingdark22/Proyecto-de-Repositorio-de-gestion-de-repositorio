<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function conn(): string
    {
        return config('dual_database.repositorio_connection', 'pgsql');
    }

    public function up(): void
    {
        Schema::connection($this->conn())->create('org_contactos', function (Blueprint $table) {
            $table->bigIncrements('oco_codigo');
            $table->integer('org_codigo');
            $table->integer('dep_codigo')->nullable();
            $table->string('oco_nombre', 255);
            $table->string('oco_apellido', 255)->nullable();
            $table->string('oco_correo', 255)->nullable();
            $table->string('oco_telefono', 255)->nullable();
            $table->string('oco_cargo', 255)->nullable();
            $table->timestamps();

            $table->foreign('org_codigo')->references('org_codigo')->on('organizacion')->onDelete('cascade');
            $table->foreign('dep_codigo')->references('dep_codigo')->on('departamento')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::connection($this->conn())->dropIfExists('org_contactos');
    }
};
