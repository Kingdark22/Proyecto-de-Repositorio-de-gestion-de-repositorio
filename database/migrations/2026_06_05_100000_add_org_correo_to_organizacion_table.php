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
        Schema::connection($this->conn())->table('organizacion', function (Blueprint $table) {
            $table->string('org_correo', 255)->nullable()->after('org_rif');
        });
    }

    public function down(): void
    {
        Schema::connection($this->conn())->table('organizacion', function (Blueprint $table) {
            $table->dropColumn('org_correo');
        });
    }
};
