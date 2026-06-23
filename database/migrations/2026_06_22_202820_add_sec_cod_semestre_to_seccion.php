<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function connections(): array
    {
        $conns = [];
        $intranet = config('dual_database.intranet_connection', 'intranet');
        $simulacion = config('dual_database.simulation_connection', 'simulacion');
        foreach ([$intranet, $simulacion] as $conn) {
            try {
                DB::connection($conn)->getPdo();
                $conns[] = $conn;
            } catch (\Throwable) {
            }
        }
        return $conns;
    }

    public function up(): void
    {
        foreach ($this->connections() as $conn) {
            $schema = Schema::connection($conn);
            if ($schema->hasTable('seccion') && !$schema->hasColumn('seccion', 'sec_cod_semestre')) {
                DB::connection($conn)->statement('ALTER TABLE seccion ADD COLUMN sec_cod_semestre INT DEFAULT NULL AFTER sec_cod_malla');
                DB::connection($conn)->statement('ALTER TABLE seccion ADD INDEX idx_sec_cod_semestre (sec_cod_semestre)');
            }
        }
    }

    public function down(): void
    {
        foreach ($this->connections() as $conn) {
            $schema = Schema::connection($conn);
            if ($schema->hasTable('seccion') && $schema->hasColumn('seccion', 'sec_cod_semestre')) {
                DB::connection($conn)->statement('ALTER TABLE seccion DROP INDEX idx_sec_cod_semestre');
                DB::connection($conn)->statement('ALTER TABLE seccion DROP COLUMN sec_cod_semestre');
            }
        }
    }
};
