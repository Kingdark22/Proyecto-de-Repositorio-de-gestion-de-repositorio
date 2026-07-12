<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$conn = \App\Helpers\DualDatabase::academicConnection();
echo "Connection: $conn\n\n";

// Get all columns
$cols = DB::connection($conn)->select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'persona' ORDER BY ordinal_position");
echo "Columns:\n";
foreach ($cols as $c) {
    echo "  {$c->column_name} ({$c->data_type}, nullable={$c->is_nullable})\n";
}

// Get the full record for this user
echo "\nRecord for 31306741:\n";
$p = \App\Helpers\DualDatabase::table('persona')->whereRaw('TRIM(per_cedula) = ?', ['31306741'])->first();
if ($p) {
    foreach ($p as $k => $v) {
        echo "  $k: " . var_export($v, true) . "\n";
    }
} else {
    echo "  Not found\n";
}
