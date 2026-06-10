<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$svc = app(App\Services\IntranetProfessorService::class);
$lap = $svc->lapsoVigenteCodigo();
echo "Lapso vigente: ".($lap ?? 'ninguno').PHP_EOL;

$pag = $svc->paginateDocentes('', $lap, [], 5, 1);
echo 'Docentes UC proyecto en lapso: '.$pag->total().PHP_EOL;
foreach ($pag->items() as $doc) {
    echo trim($doc->cedula).' | '.$doc->lapso_nombre.' | '.$doc->trayecto_nombre.' | asig='.$doc->asignaciones->count().PHP_EOL;
}
