<?php

namespace App\Console\Commands;

use App\Models\Proyecto;
use Illuminate\Console\Command;

class CleanupOrphanProjects extends Command
{
    protected $signature = 'app:cleanup-orphan-projects {--dry-run : Solo listar, no eliminar} {--force : Eliminar sin confirmar}';
    protected $description = 'Elimina proyectos cuyo equipo/grupo referenciado (equipo_ref) ya no existe en la BD';

    public function handle(): int
    {
        $proyectos = Proyecto::whereNotNull('equipo_ref')
            ->where('equipo_ref', '!=', '')
            ->get();

        $orphans = [];
        foreach ($proyectos as $p) {
            if ($p->equipo_resumen === '—') {
                $orphans[] = $p;
            }
        }

        if (empty($orphans)) {
            $this->info('✓ No hay proyectos huérfanos.');
            return 0;
        }

        $this->warn("Se encontraron " . count($orphans) . " proyecto(s) huérfano(s):");
        $this->newLine();

        foreach ($orphans as $p) {
            $this->line("  [{$p->id}] {$p->equipo_ref} — \"{$p->titulo}\" ({$p->pry_estado})");
        }

        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info('✅ Dry-run: no se eliminó nada.');
            return 0;
        }

        if (!$this->option('force') && !$this->confirm('¿Eliminar permanentemente estos proyectos?')) {
            $this->warn('Operación cancelada.');
            return 1;
        }

        $bar = $this->output->createProgressBar(count($orphans));
        $bar->start();

        foreach ($orphans as $p) {
            try {
                $p->documentos()->delete();
                $p->delete();
            } catch (\Throwable $e) {
                $this->warn("  Error al eliminar proyecto {$p->id}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ " . count($orphans) . " proyecto(s) eliminado(s) permanentemente.");

        return 0;
    }
}
