<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'allow_free='.(config('roles.allow_free_session_roles') ? 'yes' : 'no')."\n";

$user = App\Models\User::query()->first();
if (! $user) {
    echo "No user\n";
    exit(1);
}

$svc = app(App\Services\UserRoleService::class);
foreach (['estudiante', 'administrador', 'coordinacion', 'docente'] as $key) {
    $ok = $svc->setActiveRoleByModuleKey($user, $key);
    $active = $svc->getActiveRole($user);
    echo "{$key} => ".($ok ? 'ok' : 'fail')." active={$active}\n";
}
