<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$conn = App\Helpers\DbHelper::connection();
echo "active={$conn}\n";
if (Illuminate\Support\Facades\Schema::connection($conn)->hasTable('seccion_unidad_docente')) {
    echo implode(', ', Illuminate\Support\Facades\Schema::connection($conn)->getColumnListing('seccion_unidad_docente'))."\n";
}
