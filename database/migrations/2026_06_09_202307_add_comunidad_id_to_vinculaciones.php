<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vinculaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('comunidad_id')->nullable()->after('tipo');
            $table->index('comunidad_id');
        });
    }

    public function down(): void
    {
        Schema::table('vinculaciones', function (Blueprint $table) {
            $table->dropIndex(['comunidad_id']);
            $table->dropColumn('comunidad_id');
        });
    }
};
