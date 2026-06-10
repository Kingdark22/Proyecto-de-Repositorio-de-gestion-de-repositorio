<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InspectDatabases extends Command
{
    protected $signature = 'app:inspect-databases';

    protected $description = 'Lista tablas en repositorio e intranet';

    public function handle(): int
    {
        $this->info('MySQL repositorio:');
        foreach (Schema::connection('mysql')->getTableListing() as $table) {
            $this->line('  - ' . $table);
        }

        $this->info('Intranet:');
        try {
            $rows = DB::connection('intranet')->select(
                "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name"
            );
            foreach ($rows as $row) {
                $this->line('  - ' . $row->table_name);
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }

        return 0;
    }
}
