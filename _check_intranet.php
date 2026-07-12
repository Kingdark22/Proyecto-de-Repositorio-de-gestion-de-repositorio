<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Intranet enabled: " . var_export(config('database.connections.intranet.enabled', true), true) . "\n";
echo "Intranet host: " . config('database.connections.intranet.host') . "\n";
echo "Intranet db: " . config('database.connections.intranet.database') . "\n";

try {
    // Force connect to intranet directly
    $pdo = DB::connection('intranet')->getPdo();
    echo "Connected to intranet!\n";
    
    $cols = DB::connection('intranet')->select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'persona' ORDER BY ordinal_position");
    echo "\nColumns in persona (intranet):\n";
    foreach ($cols as $c) {
        echo "  {$c->column_name} ({$c->data_type}, nullable={$c->is_nullable})\n";
    }
    
    $p = DB::connection('intranet')->table('persona')
        ->whereRaw('TRIM(per_cedula) = ?', ['31306741'])
        ->first();
    echo "\nRecord for 31306741:\n";
    if ($p) {
        foreach ($p as $k => $v) {
            echo "  $k: " . var_export($v, true) . "\n";
        }
    } else {
        echo "  Not found\n";
    }
    
} catch (\Throwable $e) {
    echo "Cannot connect to intranet: " . $e->getMessage() . "\n";
    echo "\nFallback to simulacion:\n";
    
    $cols = DB::connection('simulacion')->select("SELECT column_name FROM information_schema.columns WHERE table_name = 'persona' ORDER BY ordinal_position");
    echo "Columns in persona (simulacion):\n";
    foreach ($cols as $c) {
        echo "  {$c->column_name}\n";
    }
}
