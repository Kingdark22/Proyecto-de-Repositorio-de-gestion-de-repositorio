<?php

namespace App\Console\Commands;

use App\Services\IntranetSimulationMirrorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MirrorAllIntranetToSimulation extends Command
{
    protected $signature = 'intranet:mirror-all
        {--tables= : Lista separada por comas de tablas específicas a espejar (ej: usuario,persona)}
        {--batch-size=500 : Registros por chunk}
        {--timeout=600 : Timeout máximo en segundos para el proceso completo}';

    protected $description = 'Espeja TODOS los datos de intranet a la base de simulación para trabajo local sin depender de intranet';

    protected IntranetSimulationMirrorService $mirror;

    public function __construct(IntranetSimulationMirrorService $mirror)
    {
        parent::__construct();
        $this->mirror = $mirror;
    }

    public function handle(): int
    {
        if (! $this->mirror->enabled()) {
            $this->error('El espejado está desactivado (INTRANET_MIRROR_TO_SIMULATION=false).');
            $this->line('Actívelo en .env o config/intranet_mirror.php y vuelva a intentar.');
            return 1;
        }

        if (! \App\Helpers\DbHelper::intranetAvailable()) {
            $this->error('No se puede conectar a la base de datos intranet.');
            $this->line('Verifique la conexión en config/database.php (conexión "intranet").');
            return 1;
        }

        $batchSize = (int) $this->option('batch-size');
        $timeout = (int) $this->option('timeout');

        if (! ini_get('safe_mode')) {
            set_time_limit($timeout);
        }

        $tablesEspecificas = $this->option('tables');

        if ($tablesEspecificas) {
            $tablas = explode(',', $tablesEspecificas);
            $tablas = array_map('trim', $tablas);
            return $this->mirrorTables($tablas, $batchSize);
        }

        // ─── Orden FK seguro ───
        // FASE 1: Catálogos base (sin FK)
        $fase1 = ['programa', 'trayecto', 'lapso_academico', 'rol', 'semestre'];

        // FASE 2: Entidades core (FK → fase 1)
        $fase2 = ['persona', 'malla', 'unidad_curricular', 'seccion'];

        // FASE 3: Usuarios y estudiantes (FK → fase 1, persona)
        $fase3 = ['usuario', 'estudiante', 'seccion_unidad_docente'];

        // FASE 4: Inscripciones (FK → estudiante, seccion_unidad_docente)
        $fase4 = ['inscripcion'];

        $totalGeneral = 0;

        $this->info('══════════════════════════════════════════════');
        $this->info('  ESPEJO COMPLETO INTRANET → SIMULACIÓN');
        $this->info('  Modo: trabajo local sin dependencia de red');
        $this->info('══════════════════════════════════════════════');
        $this->newLine();

        foreach ([
            'FASE 1: Catálogos base'   => $fase1,
            'FASE 2: Entidades core'   => $fase2,
            'FASE 3: Usuarios'         => $fase3,
            'FASE 4: Inscripciones'    => $fase4,
        ] as $fase => $tablas) {
            $this->info("▶ {$fase}");
            $this->newLine();

            foreach ($tablas as $tabla) {
                $resultado = $this->espejarTabla($tabla, $batchSize);
                if ($resultado['status'] === 'ok') {
                    $this->line("  ✓ {$tabla}: {$resultado['count']} filas espejadas");
                    $totalGeneral += $resultado['count'];
                } elseif ($resultado['status'] === 'no_existe') {
                    $this->warn("  - {$tabla}: no existe en simulación (se omite)");
                } elseif ($resultado['status'] === 'error') {
                    $this->error("  ✗ {$tabla}: ERROR - {$resultado['message']}");
                }
            }
            $this->newLine();
        }

        $this->info('══════════════════════════════════════════════');
        $this->info("  PROCESO COMPLETO — {$totalGeneral} filas espejadas");
        $this->info('  Ya puede trabajar sin conexión a intranet.');
        $this->info('══════════════════════════════════════════════');

        return 0;
    }

    /**
     * Espeja una tabla individual con barra de progreso.
     *
     * @return array{status: string, count?: int, message?: string}
     */
    protected function espejarTabla(string $tabla, int $batchSize): array
    {
        if (! $this->mirror->simulationHasTable($tabla)) {
            return ['status' => 'no_existe'];
        }

        try {
            // Contar registros totales en intranet para la barra de progreso
            $total = DB::connection('intranet')->table($tabla)->count();

            if ($total === 0) {
                return ['status' => 'ok', 'count' => 0];
            }

            $this->line("    ↪ {$tabla}: {$total} registros por procesar...");

            $pk = $this->getPrimaryKey($tabla);
            $espejados = 0;

            if ($pk) {
                DB::connection('intranet')->table($tabla)
                    ->orderBy($pk)
                    ->chunkById($batchSize, function ($rows) use ($tabla, &$espejados) {
                        $espejados += $this->mirror->mirrorRows($tabla, $rows);
                        $this->output->write('.');
                    }, $pk);
            } else {
                // Sin PK conocida, cargar todo (tablas pequeñas)
                $rows = DB::connection('intranet')->table($tabla)->get();
                $espejados = $this->mirror->mirrorRows($tabla, $rows);
            }

            $this->line('');
            return ['status' => 'ok', 'count' => $espejados];
        } catch (\Throwable $e) {
            Log::error("Error espejando tabla {$tabla}: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Espeja una lista específica de tablas.
     */
    protected function mirrorTables(array $tablas, int $batchSize): int
    {
        $total = 0;

        foreach ($tablas as $tabla) {
            $resultado = $this->espejarTabla($tabla, $batchSize);
            if ($resultado['status'] === 'ok') {
                $this->info("✓ {$tabla}: {$resultado['count']} filas");
                $total += $resultado['count'];
            } elseif ($resultado['status'] === 'no_existe') {
                $this->warn("- {$tabla}: no existe en simulación");
            } else {
                $this->error("✗ {$tabla}: {$resultado['message']}");
            }
        }

        $this->info("Total: {$total} filas espejadas.");
        return 0;
    }

    /**
     * Obtiene la PK de una tabla desde la configuración.
     */
    protected function getPrimaryKey(string $table): ?string
    {
        return config("intranet_mirror.primary_keys.{$table}");
    }
}
