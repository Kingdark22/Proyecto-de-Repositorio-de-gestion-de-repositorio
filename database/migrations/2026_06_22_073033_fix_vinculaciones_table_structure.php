<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vinculaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('vinculaciones', 'proyecto_id')) {
                $table->unsignedBigInteger('proyecto_id')->nullable();
                $table->index('proyecto_id');
            }
            if (!Schema::hasColumn('vinculaciones', 'vin_titulo')) {
                $table->text('vin_titulo')->nullable()->after('id');
            }
            if (!Schema::hasColumn('vinculaciones', 'vin_descripcion')) {
                $table->text('vin_descripcion')->nullable()->after('vin_titulo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vinculaciones', function (Blueprint $table) {
            $table->dropColumn(['vin_titulo', 'vin_descripcion']);
        });
    }
};
