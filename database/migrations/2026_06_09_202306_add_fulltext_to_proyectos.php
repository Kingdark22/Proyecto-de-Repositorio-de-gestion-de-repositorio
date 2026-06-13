<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected function conn(): string
    {
        return config('dual_database.repositorio_connection', 'pgsql');
    }

    public function up(): void
    {
        DB::connection($this->conn())->statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS ft_proyectos_resumen_gin ON proyectos USING GIN (to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')))');
    }

    public function down(): void
    {
        DB::connection($this->conn())->statement('DROP INDEX IF EXISTS ft_proyectos_resumen_gin');
    }
};
