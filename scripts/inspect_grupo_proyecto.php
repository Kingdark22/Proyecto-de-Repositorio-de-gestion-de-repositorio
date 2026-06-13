<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['intranet', 'simulacion'] as $conn) {
    try {
        Illuminate\Support\Facades\DB::connection($conn)->getPdo();
    } catch (Throwable $e) {
        echo "\n=== {$conn} (sin conexion) ===\n";
        continue;
    }
echo "\n=== {$conn} ===\n";
foreach (['grupo_proyecto', 'grupo_proyecto_estudiante', 'equipos', 'equipo_estudiante'] as $t) {
    if (Illuminate\Support\Facades\Schema::connection($conn)->hasTable($t)) {
        echo "{$t}: ".implode(', ', Illuminate\Support\Facades\Schema::connection($conn)->getColumnListing($t))."\n";
    } else {
        echo "{$t}: (no existe)\n";
    }
}
}
