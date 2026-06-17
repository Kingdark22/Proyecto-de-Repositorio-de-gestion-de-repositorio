<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('pgsql')->table('proyectos', function (Blueprint $table) {
            $table->string('pry_titulo', 500)->nullable()->after('pry_codigo');
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql')->table('proyectos', function (Blueprint $table) {
            $table->dropColumn('pry_titulo');
        });
    }
};
