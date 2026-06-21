<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TODOS LOS USUARIOS EN SIMULACION ===\n";
$users = DB::connection('simulacion')
    ->table('usuario')
    ->select(['usu_cedula', 'usu_nombre', 'usu_clave'])
    ->get();

foreach ($users as $u) {
    $cedula = trim($u->usu_cedula ?? '');
    $hash = trim($u->usu_clave ?? '');
    echo "Cédula: [{$cedula}] Nombre: [{$u->usu_nombre}]\n";
    echo "  Hash: {$hash}\n";
    echo "  verify(cedula):     " . (password_verify($cedula, $hash) ? 'PASA' : 'FALLA') . "\n";
    echo "  verify(cedula+ent): " . (password_verify($cedula . 'ent', $hash) ? 'PASA' : 'FALLA') . "\n";
    echo "  verify(12345678):   " . (password_verify('12345678', $hash) ? 'PASA' : 'FALLA') . "\n";
    echo "  verify(admin):      " . (password_verify('admin', $hash) ? 'PASA' : 'FALLA') . "\n";
    echo "  verify(Admin123):   " . (password_verify('Admin123', $hash) ? 'PASA' : 'FALLA') . "\n";
    echo "  verify(password):   " . (password_verify('password', $hash) ? 'PASA' : 'FALLA') . "\n";
    echo "  verify(123456):     " . (password_verify('123456', $hash) ? 'PASA' : 'FALLA') . "\n";
    
    // Try a few other common passwords
    foreach (['sogac2024', 'sogac2025', 'repositorio', 'uptp', 'uptp2024', 'uptp2025', 'admin123', 'Admin1234', 'root', '1234', 'upto2024', 'upto2025'] as $common) {
        if (password_verify($common, $hash)) {
            echo "  *** PASA CON: {$common} ***\n";
        }
    }
    echo "\n";
}

echo "\n=== PROBAR HACER LOGIN CON LARAVEL AUTH ===\n";
// Try the user 13354832 with Auth::attempt
try {
    $userModel = \App\Models\User::on('simulacion')
        ->whereRaw('TRIM(usu_cedula) = ?', ['13354832'])
        ->first();
    
    if ($userModel) {
        echo "User model encontrado. Intentando Auth::login()...\n";
        Auth::login($userModel);
        echo "  Auth::check(): " . (Auth::check() ? 'true (autenticado)' : 'false') . "\n";
        echo "  User ID: " . Auth::id() . "\n";
        Auth::logout();
    }
} catch (\Throwable $e) {
    echo "  ERROR en Auth::login: " . $e->getMessage() . "\n";
}
