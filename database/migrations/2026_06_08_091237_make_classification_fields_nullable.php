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
        Schema::table('proyectos', function (Blueprint $table) {
            $table->bigInteger('lin_codigo')->nullable()->change();
            $table->bigInteger('mei_codigo')->nullable()->change();
            $table->bigInteger('tpu_codigo')->nullable()->change();
            $table->bigInteger('tin_codigo')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->bigInteger('lin_codigo')->nullable(false)->change();
            $table->bigInteger('mei_codigo')->nullable(false)->change();
            $table->bigInteger('tpu_codigo')->nullable(false)->change();
            $table->bigInteger('tin_codigo')->nullable(false)->change();
        });
    }
};
