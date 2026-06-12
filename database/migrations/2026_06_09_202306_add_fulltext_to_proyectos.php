<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX ft_proyectos_resumen_gin ON proyectos USING GIN (to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')))');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS ft_proyectos_resumen_gin');
    }
};
