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
            $table->bigInteger('objetivo_investigacion_id')->nullable()->after('tipo_investigacion_id');
            $table->foreign('objetivo_investigacion_id')->references('obi_codigo')->on('objetivo_investigacions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropForeign(['objetivo_investigacion_id']);
            $table->dropColumn('objetivo_investigacion_id');
        });
    }
};

