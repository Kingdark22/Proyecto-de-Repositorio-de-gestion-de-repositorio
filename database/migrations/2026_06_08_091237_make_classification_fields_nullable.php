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
        $c = $this->conn();
        DB::connection($c)->statement('ALTER TABLE proyectos ALTER COLUMN lin_codigo DROP NOT NULL');
        DB::connection($c)->statement('ALTER TABLE proyectos ALTER COLUMN mei_codigo DROP NOT NULL');
        DB::connection($c)->statement('ALTER TABLE proyectos ALTER COLUMN tpu_codigo DROP NOT NULL');
        DB::connection($c)->statement('ALTER TABLE proyectos ALTER COLUMN tin_codigo DROP NOT NULL');
    }

    public function down(): void
    {
        $c = $this->conn();
        DB::connection($c)->statement('ALTER TABLE proyectos ALTER COLUMN lin_codigo SET NOT NULL');
        DB::connection($c)->statement('ALTER TABLE proyectos ALTER COLUMN mei_codigo SET NOT NULL');
        DB::connection($c)->statement('ALTER TABLE proyectos ALTER COLUMN tpu_codigo SET NOT NULL');
        DB::connection($c)->statement('ALTER TABLE proyectos ALTER COLUMN tin_codigo SET NOT NULL');
    }
};
