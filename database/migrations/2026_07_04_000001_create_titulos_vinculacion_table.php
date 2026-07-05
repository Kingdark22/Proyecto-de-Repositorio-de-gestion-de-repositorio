<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titulos_vinculacion', function (Blueprint $table) {
            $table->id('tiv_codigo');
            $table->text('tiv_titulo');
            $table->boolean('tiv_estado_logico')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulos_vinculacion');
    }
};
