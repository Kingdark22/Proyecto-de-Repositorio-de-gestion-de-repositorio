<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE proyectos ADD FULLTEXT INDEX ft_proyectos_titulo_resumen (pry_titulo, pry_resumen)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE proyectos DROP INDEX ft_proyectos_titulo_resumen');
    }
};
