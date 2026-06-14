<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comunidades', function ($table) {
            $table->string('com_telefono', 50)->nullable()->after('com_correo');
        });
    }

    public function down(): void
    {
        Schema::table('comunidades', function ($table) {
            $table->dropColumn('com_telefono');
        });
    }
};
