<?php

namespace App\Console\Commands;

use App\Services\IntranetSimulationMirrorService;
use Illuminate\Console\Command;

class MirrorAcademicCatalogs extends Command
{
    protected $signature = 'app:mirror-academic-catalogs {--table= : Tabla específica para espejar}';
    protected $description = 'Espeja los catálogos académicos desde intranet a la base de datos de simulación.';

    public function handle()
    {
        $service = app(IntranetSimulationMirrorService::class);

        if (!$service->shouldMirrorFromIntranet()) {
            $this->error('El espejado no está habilitado o no se está usando la conexión de intranet.');
            return 1;
        }

        $table = $this->option('table');

        if ($table) {
            $this->info("Espejando tabla: {$table}...");
            $count = $service->mirrorTable($table);
            $this->info("✓ Se espejaron {$count} filas en {$table}.");
        } else {
            $this->info('Espejando todos los catálogos académicos...');
            $results = $service->mirrorAllCatalogs();

            foreach ($results as $tbl => $count) {
                $this->line("- {$tbl}: {$count} filas.");
            }
            $this->info('✓ Proceso de espejado completo.');
        }

        return 0;
    }
}
