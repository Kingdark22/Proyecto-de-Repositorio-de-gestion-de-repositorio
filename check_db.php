<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SIMULACION DB ===\n";
try {
    $tables = DB::connection('simulacion')->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE' ORDER BY table_name");
    echo "Tables:\n";
    foreach ($tables as $t) {
        echo "  - " . $t->table_name . "\n";
    }
    $userCount = DB::connection('simulacion')->table('usuario')->count();
    echo "\nUsuarios en simulacion: $userCount\n";
    $users = DB::connection('simulacion')->table('usuario')->select(['usu_cedula', 'usu_nombre', 'usu_estatus'])->limit(15)->get();
    foreach ($users as $u) {
        echo "  [" . trim($u->usu_cedula) . "] " . trim($u->usu_nombre) . " (estatus=" . trim($u->usu_estatus ?? '?') . ")\n";
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== INTRANET DB ===\n";
try {
    $tables = DB::connection('intranet')->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE' ORDER BY table_name");
    echo "Tables:\n";
    foreach ($tables as $t) {
        echo "  - " . $t->table_name . "\n";
    }
    $userCount = DB::connection('intranet')->table('usuario')->count();
    echo "\nUsuarios en intranet: $userCount\n";
    $users = DB::connection('intranet')->table('usuario')->select(['usu_cedula', 'usu_nombre', 'usu_estatus'])->limit(15)->get();
    foreach ($users as $u) {
        echo "  [" . trim($u->usu_cedula) . "] " . trim($u->usu_nombre) . " (estatus=" . trim($u->usu_estatus ?? '?') . ")\n";
    }
    echo "\nTotal de usuarios activos en intranet: " . DB::connection('intranet')->table('usuario')->where('usu_estatus', 'A')->count() . "\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
