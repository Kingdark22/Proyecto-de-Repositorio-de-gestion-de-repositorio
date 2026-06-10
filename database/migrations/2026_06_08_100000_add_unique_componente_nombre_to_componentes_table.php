<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('componentes', function (Blueprint $table) {
            $table->unique(['comp_nombre', 'coord_codigo'], 'uq_componentes_nombre_programa');
        });
    }

    public function down(): void
    {
        Schema::table('componentes', function (Blueprint $table) {
            $table->dropUnique('uq_componentes_nombre_programa');
        });
    }
};
