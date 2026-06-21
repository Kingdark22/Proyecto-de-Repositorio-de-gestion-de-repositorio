<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$conn = 'intranet';
$tables = ['seccion_unidad_docente', 'rol', 'estado', 'municipio', 'parroquia'];

foreach ($tables as $t) {
    try {
        $has = Illuminate\Support\Facades\Schema::connection($conn)->hasTable($t);
        if (!$has) { echo "=== $t === NO EXISTE\n\n"; continue; }
        $cols = Illuminate\Support\Facades\Schema::connection($conn)->getColumnListing($t);
        echo "=== $t ===\n";
        echo implode(', ', $cols) . "\n";
        $count = Illuminate\Support\Facades\DB::connection($conn)->table($t)->count();
        echo "Rows: $count\n\n";
    } catch (Throwable $e) {
        echo "=== $t === ERROR: " . $e->getMessage() . "\n\n";
    }
}
