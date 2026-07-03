<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('proyecto_documentos', function (Blueprint $table) {
            $table->integer('pd_estado')->default(0)->comment('0:pendiente, 1:aceptado, 2:rechazado');
            $table->text('pd_observacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyecto_documentos', function (Blueprint $table) {
            $table->integer('pd_estado')->default(0)->comment('0:pendiente, 1:aceptado, 2:rechazado');
            $table->text('pd_observacion')->nullable();
        });
    }
};
