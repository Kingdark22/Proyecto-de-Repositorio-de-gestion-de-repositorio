<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS idx_proyectos_direccion_logica ON proyectos (pry_direccion_logica)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_proyectos_creador_cedula ON proyectos (pry_creador_cedula)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_proyectos_estado_validacion_logico ON proyectos (pry_estado_validacion, pry_estado_logico)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_proyecto_documentos_estado ON proyecto_documentos (pd_estado)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_grupo_identificador ON grupo_proyecto_modulo (grp_identificador)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_proyectos_direccion_logica');
        DB::statement('DROP INDEX IF EXISTS idx_proyectos_creador_cedula');
        DB::statement('DROP INDEX IF EXISTS idx_proyectos_estado_validacion_logico');
        DB::statement('DROP INDEX IF EXISTS idx_proyecto_documentos_estado');
        DB::statement('DROP INDEX IF EXISTS idx_grupo_identificador');
    }
};
