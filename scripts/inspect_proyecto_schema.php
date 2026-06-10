<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Illuminate\Support\Facades\Schema::getColumnListing('proyectos');
print_r($columns);
print_r(Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE 'proyecto%';"));
