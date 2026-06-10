<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimulacionAcademicaSeeder extends Seeder
{
    public function run(): void
    {
        $conn = 'simulacion';

        // Trayectos I-V
        $trayectos = [
            ['tra_codigo' => 1, 'tra_nombre' => 'I', 'tra_estatus' => 'A'],
            ['tra_codigo' => 2, 'tra_nombre' => 'II', 'tra_estatus' => 'A'],
            ['tra_codigo' => 3, 'tra_nombre' => 'III', 'tra_estatus' => 'A'],
            ['tra_codigo' => 4, 'tra_nombre' => 'IV', 'tra_estatus' => 'A'],
            ['tra_codigo' => 5, 'tra_nombre' => 'V', 'tra_estatus' => 'A'],
        ];

        foreach ($trayectos as $t) {
            DB::connection($conn)->table('trayecto')->updateOrInsert(
                ['tra_codigo' => $t['tra_codigo']],
                $t
            );
        }

        // Malla: each program gets entries for trayectos I-V
        $existingMalla = DB::connection($conn)->table('malla')->pluck('mal_codigo')->toArray();
        $nextMalCodigo = $existingMalla ? max($existingMalla) + 1 : 1;

        $mallaInserts = [];
        for ($prog = 1; $prog <= 10; $prog++) {
            for ($tray = 1; $tray <= 5; $tray++) {
                $exists = DB::connection($conn)->table('malla')
                    ->where('mal_cod_programa', $prog)
                    ->where('mal_cod_trayecto', $tray)
                    ->exists();
                if (!$exists) {
                    $mallaInserts[] = [
                        'mal_codigo' => $nextMalCodigo++,
                        'mal_nombre' => "PROG{$prog}-T{$tray}",
                        'mal_cod_programa' => $prog,
                        'mal_cod_trayecto' => $tray,
                        'mal_estatus' => 'A',
                    ];
                }
            }
        }

        if (!empty($mallaInserts)) {
            DB::connection($conn)->table('malla')->insert($mallaInserts);
        }

        // Secciones: create 2 sample secciones per program (using the first malla entry for each program)
        $mallas = DB::connection($conn)->table('malla')
            ->where('mal_cod_trayecto', 1)
            ->whereIn('mal_cod_programa', range(1, 10))
            ->get();

        $existingSecciones = DB::connection($conn)->table('seccion')->pluck('sec_codigo')->toArray();
        $nextSecCodigo = $existingSecciones ? max($existingSecciones) + 1 : 1;

        $seccionInserts = [];
        foreach ($mallas as $malla) {
            for ($i = 1; $i <= 2; $i++) {
                $exists = DB::connection($conn)->table('seccion')
                    ->where('sec_cod_malla', $malla->mal_codigo)
                    ->where('sec_nombre', "0{$i}1     ")
                    ->exists();
                if (!$exists) {
                    $seccionInserts[] = [
                        'sec_codigo' => $nextSecCodigo++,
                        'sec_nombre' => "0{$i}1     ",
                        'sec_cod_lapso_academico' => 63,
                        'sec_cod_malla' => $malla->mal_codigo,
                        'sec_capacidad' => 45,
                        'sec_inscritos' => 30,
                        'sec_estatus' => 'A',
                    ];
                }
            }
        }

        if (!empty($seccionInserts)) {
            DB::connection($conn)->table('seccion')->insert($seccionInserts);
        }

        $this->command->info('Simulación académica: trayectos, malla y secciones poblados para todos los programas.');
    }
}
