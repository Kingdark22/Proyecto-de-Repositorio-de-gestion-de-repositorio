<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function conn(): string
    {
        return (string) config('dual_database.repositorio_connection', 'pgsql');
    }

    public function up(): void
    {
        if (!Schema::connection($this->conn())->hasColumn('comunidad_contactos', 'com_codigo')) {
            Schema::connection($this->conn())->table('comunidad_contactos', function (Blueprint $table) {
                $table->bigInteger('com_codigo')->nullable(false)->after('ccom_codigo');
            });
        }

        try {
            Schema::connection($this->conn())->table('comunidad_contactos', function (Blueprint $table) {
                $table->foreign('com_codigo')->references('com_codigo')->on('comunidades')->cascadeOnDelete();
            });
        } catch (Exception $e) {
            // FK may already exist
        }
    }

    public function down(): void
    {
        if (Schema::connection($this->conn())->hasColumn('comunidad_contactos', 'com_codigo')) {
            Schema::connection($this->conn())->table('comunidad_contactos', function (Blueprint $table) {
                $table->dropForeign(['com_codigo']);
                $table->dropColumn('com_codigo');
            });
        }
    }
};
