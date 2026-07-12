<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comunidades', function ($table) {
            if (Schema::hasColumn('comunidades', 'anio')) {
                $table->dropColumn('anio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('comunidades', function ($table) {
            $table->integer('anio')->nullable();
        });
    }
};
