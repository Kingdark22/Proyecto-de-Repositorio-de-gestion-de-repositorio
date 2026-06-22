<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('vinculaciones', 'proyecto_id')) {
            Schema::table('vinculaciones', function (Blueprint $table) {
                $table->dropColumn('proyecto_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('vinculaciones', 'proyecto_id')) {
            Schema::table('vinculaciones', function (Blueprint $table) {
                $table->unsignedBigInteger('proyecto_id')->nullable();
            });
        }
    }
};
