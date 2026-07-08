<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Registrar comandos personalizados
Artisan::command('app:generate-login-link {user? : El usuario o cedula}', function () {
    $this->call(\App\Console\Commands\GenerateLoginLink::class, [
        'user' => $this->argument('user'),
    ]);
})->purpose('Genera un enlace de acceso directo verificando el usuario/cedula');
