<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE SEQUENCE IF NOT EXISTS direcciones_dir_codigo_seq START WITH 1 OWNED BY direcciones.dir_codigo");
        DB::statement("ALTER TABLE direcciones ALTER COLUMN dir_codigo SET DEFAULT nextval('direcciones_dir_codigo_seq')");
        DB::statement("SELECT setval('direcciones_dir_codigo_seq', COALESCE((SELECT MAX(dir_codigo) FROM direcciones), 1))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE direcciones ALTER COLUMN dir_codigo DROP DEFAULT");
        DB::statement("DROP SEQUENCE IF EXISTS direcciones_dir_codigo_seq");
    }
};
