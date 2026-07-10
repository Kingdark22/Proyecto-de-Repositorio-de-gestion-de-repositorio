<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->renameColumn('pry_personas_beneficiadas', 'pry_cantidad_beneficiados');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->renameColumn('pry_cantidad_beneficiados', 'pry_personas_beneficiadas');
        });
    }
};
