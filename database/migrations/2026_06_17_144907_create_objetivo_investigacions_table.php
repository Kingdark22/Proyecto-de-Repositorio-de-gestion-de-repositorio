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
        Schema::create('objetivo_investigacions', function (Blueprint $table) {
            $table->bigIncrements('obi_codigo');
            $table->string('obi_nombre', 255);
            $table->text('obi_descripcion')->nullable();
            $table->boolean('obi_estado_logico')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objetivo_investigacions');
    }
};
