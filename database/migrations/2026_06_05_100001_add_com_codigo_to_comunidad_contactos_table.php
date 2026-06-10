<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::connection('mysql')->hasColumn('comunidad_contactos', 'com_codigo')) {
            Schema::connection('mysql')->table('comunidad_contactos', function (Blueprint $table) {
                $table->bigInteger('com_codigo')->nullable(false)->after('ccom_codigo');
            });
        }

        try {
            Schema::connection('mysql')->table('comunidad_contactos', function (Blueprint $table) {
                $table->foreign('com_codigo')->references('com_codigo')->on('comunidades')->cascadeOnDelete();
            });
        } catch (Exception $e) {
            // FK may already exist
        }
    }

    public function down(): void
    {
        if (Schema::connection('mysql')->hasColumn('comunidad_contactos', 'com_codigo')) {
            Schema::connection('mysql')->table('comunidad_contactos', function (Blueprint $table) {
                $table->dropForeign(['com_codigo']);
                $table->dropColumn('com_codigo');
            });
        }
    }
};
