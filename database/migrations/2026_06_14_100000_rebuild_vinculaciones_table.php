<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('vinculaciones');

        Schema::create('vinculaciones', function (Blueprint $table) {
            $table->bigIncrements('vin_codigo');
            $table->unsignedBigInteger('pry_codigo');
            $table->string('vin_tipo', 255);
            $table->unsignedBigInteger('com_codigo')->nullable();
            $table->text('vin_observaciones')->nullable();
            $table->boolean('vin_estado_logico')->default(true);
            $table->timestamps();

            $table->index('pry_codigo');
            $table->index('com_codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinculaciones');

        Schema::create('vinculaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proyecto_id')->nullable();
            $table->string('tipo', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('proyecto_id');
        });
    }
};
