<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$conn = config('dual_database.repositorio_connection', 'pgsql');
$db = Illuminate\Support\Facades\DB::connection($conn);
foreach (['comunidades', 'estados', 'municipios', 'direcciones', 'comunidad_contactos', 'roles'] as $t) {
    $exists = Illuminate\Support\Facades\Schema::connection($conn)->hasTable($t);
    echo $t.': '.($exists ? 'SI' : 'NO');
    if ($exists) {
        echo ' cols='.implode(',', Illuminate\Support\Facades\Schema::connection($conn)->getColumnListing($t));
    }
    echo PHP_EOL;
}
