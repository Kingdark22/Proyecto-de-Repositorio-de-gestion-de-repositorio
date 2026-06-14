<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'comunidades'              => 'com_codigo',
        'proyectos'                => 'pry_codigo',
        'componentes'              => 'comp_codigo',
        'estados'                  => 'est_codigo',
        'municipios'               => 'mun_codigo',
        'grupo_proyecto_modulo'    => 'grp_codigo',
        'linea_investigacions'     => 'lin_codigo',
        'metodologia_investigacions' => 'mei_codigo',
        'tipo_investigacions'      => 'tin_codigo',
        'tipo_publicacions'        => 'tpu_codigo',
        'proyectos_publicados'     => 'pub_codigo',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $pk) {
            $seq = "{$table}_{$pk}_seq";
            DB::statement("CREATE SEQUENCE IF NOT EXISTS {$seq} OWNED BY {$table}.{$pk}");
            DB::statement("ALTER TABLE {$table} ALTER COLUMN {$pk} SET DEFAULT nextval('{$seq}')");
            DB::statement("SELECT setval('{$seq}', COALESCE((SELECT MAX({$pk}) FROM {$table}), 1))");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $pk) {
            $seq = "{$table}_{$pk}_seq";
            DB::statement("ALTER TABLE {$table} ALTER COLUMN {$pk} DROP DEFAULT");
            DB::statement("DROP SEQUENCE IF EXISTS {$seq}");
        }
    }
};
