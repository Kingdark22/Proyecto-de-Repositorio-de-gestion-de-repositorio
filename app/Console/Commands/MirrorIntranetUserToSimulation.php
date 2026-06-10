<?php

namespace App\Console\Commands;

use App\Services\IntranetSimulationMirrorService;
use Illuminate\Console\Command;

class MirrorIntranetUserToSimulation extends Command
{
    protected $signature = 'app:mirror-intranet-user {cedula : Cédula del usuario}';

    protected $description = 'Copia contexto académico intranet de un usuario hacia la BD de simulación';

    public function handle(IntranetSimulationMirrorService $mirror): int
    {
        if (! $mirror->enabled()) {
            $this->warn('Espejo desactivado (INTRANET_MIRROR_TO_SIMULATION=false).');

            return 1;
        }

        $cedula = trim((string) $this->argument('cedula'));
        $count = $mirror->mirrorUserContext($cedula);

        $this->info("Filas espejadas (aprox.): {$count} para cédula {$cedula}");

        return 0;
    }
}
