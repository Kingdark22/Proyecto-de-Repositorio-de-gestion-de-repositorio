<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'count='.App\Models\Comunidad::count().PHP_EOL;
$c = App\Models\Comunidad::orderByDesc('id')->first();
echo $c ? ($c->id.' '.$c->nombre.' '.$c->direccion).PHP_EOL : "empty\n";
