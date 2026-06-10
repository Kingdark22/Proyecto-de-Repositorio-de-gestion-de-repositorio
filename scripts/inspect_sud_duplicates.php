<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$conn = App\Helpers\DbHelper::connection();
$prefijos = config('proyecto_profesor.unidad_siglas_prefijos', []);

$q = Illuminate\Support\Facades\DB::connection($conn)
    ->table('seccion_unidad_docente as sud')
    ->join('unidad_curricular as ucu', 'ucu.ucu_codigo', '=', 'sud.sud_cod_unidad')
    // ->where('sud.sud_estatus', config('proyecto_profesor.sud_estatus_activo', 'A'));

$q->where(function ($sub) use ($prefijos) {
    foreach ($prefijos as $prefijo) {
        $sub->orWhereRaw('TRIM(ucu.ucu_siglas) LIKE ?', [trim($prefijo).'%']);
    }
});

$rows = $q->selectRaw('sud.sud_cod_seccion, sud.sud_cod_unidad, COUNT(*) as total, MAX(sud.sud_codigo) as max_cod')
    ->groupBy('sud.sud_cod_seccion', 'sud.sud_cod_unidad')
    ->havingRaw('COUNT(*) > 1')
    ->orderByDesc('total')
    ->limit(10)
    ->get();

echo "Secciones con mas de un sud activo en UC proyecto: ".$rows->count()."\n";
foreach ($rows as $r) {
    echo "sec={$r->sud_cod_seccion} ucu={$r->sud_cod_unidad} total={$r->total} max_sud={$r->max_cod}\n";
}
