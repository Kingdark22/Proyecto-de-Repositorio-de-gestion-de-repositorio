<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$conn = App\Helpers\DbHelper::connection();
echo "Connection: {$conn}\n";

$rows = Illuminate\Support\Facades\DB::connection($conn)
    ->table('unidad_curricular')
    ->where(function ($q) {
        $q->whereRaw('LOWER(ucu_nombre) LIKE ?', ['%proyecto%'])
            ->orWhereRaw('LOWER(ucu_siglas) LIKE ?', ['%proy%']);
    })
    ->select('ucu_codigo', 'ucu_siglas', 'ucu_nombre')
    ->orderBy('ucu_siglas')
    ->limit(40)
    ->get();

foreach ($rows as $r) {
    echo "{$r->ucu_codigo} | {$r->ucu_siglas} | {$r->ucu_nombre}\n";
}
echo 'Found: '.$rows->count()."\n";

$lap = Illuminate\Support\Facades\DB::connection($conn)
    ->table('lapso_academico')
    ->where('lap_estatus', 'A')
    ->orderByDesc('lap_codigo')
    ->first();
echo 'Lapso vigente: '.($lap ? $lap->lap_codigo.' '.$lap->lap_nombre : 'ninguno')."\n";
