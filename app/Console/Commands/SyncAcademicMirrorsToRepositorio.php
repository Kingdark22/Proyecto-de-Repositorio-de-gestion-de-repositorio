<?php

namespace App\Console\Commands;

use App\Services\AcademicCatalog;
use Illuminate\Console\Command;

class SyncAcademicMirrorsToRepositorio extends Command
{
    protected $signature = 'app:sync-academic-mirrors';

    protected $description = 'Obsoleto: no se copian tablas académicas a repositorio. Use espejo intranet→simulación.';

    public function handle(AcademicCatalog $catalog): int
    {
        $this->warn('Este comando ya no escribe en repositorio.');
        $this->line('Lectura académica: intranet (o simulación si cae).');
        $this->line('Escritura del módulo: solo MySQL repositorio.');
        $this->line('Respaldo: php artisan app:mirror-intranet-user {cedula} (intranet → simulación).');

        return 0;
    }
}
