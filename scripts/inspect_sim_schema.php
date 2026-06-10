<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = Illuminate\Support\Facades\Schema::connection('simulacion');
foreach (['usuario', 'persona', 'estudiante', 'lapso_academico', 'seccion_unidad_docente', 'inscripcion', 'seccion', 'malla', 'unidad_curricular'] as $t) {
    if (! $s->hasTable($t)) {
        echo "{$t}: NO\n";
        continue;
    }
    echo "{$t}: ".implode(', ', $s->getColumnListing($t))."\n";
}
