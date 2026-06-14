<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== REPOSITORIO DB ===\n";
$all = DB::connection('pgsql')->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname='public' ORDER BY tablename");
echo "=== ALL TABLES ===\n";
foreach ($all as $t) {
    echo "  " . $t->tablename . "\n";
}

echo "\n=== COLUMNS PER TABLE ===\n";
foreach ($all as $t) {
    $tname = $t->tablename;
    $cols = DB::connection('pgsql')->getSchemaBuilder()->getColumnListing($tname);
    echo "\n--- $tname ---\n";
    foreach ($cols as $c) {
        $type = DB::connection('pgsql')->select("SELECT data_type FROM information_schema.columns WHERE table_name=? AND column_name=?", [$tname, $c]);
        $tinfo = $type[0]->data_type ?? '?';
        echo "  $c ($tinfo)\n";
    }
}

echo "\n\n=== INTRANET DB ===\n";
try {
    $conn2 = config('dual_database.intranet_connection', 'pgsql');
    echo "Connection name: $conn2\n";
    $tablesIntra = DB::connection($conn2)->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname='public' ORDER BY tablename");
    echo "Tables:\n";
    foreach ($tablesIntra as $t) {
        echo "  " . $t->tablename . "\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
