<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$cedula = '13354832';
$password = '13354832';

echo "=== 1. PROBAR CONEXIONES ===\n";
foreach (['simulacion', 'intranet', 'pgsql'] as $conn) {
    try {
        DB::connection($conn)->getPdo();
        echo "  $conn: CONECTADO\n";
    } catch (\Throwable $e) {
        echo "  $conn: " . $e->getMessage() . "\n";
    }
}

echo "\n=== 2. BUSCAR USUARIO EN TODAS LAS CONEXIONES ===\n";
foreach (['simulacion', 'intranet', 'pgsql'] as $conn) {
    try {
        $user = DB::connection($conn)
            ->table('usuario')
            ->where('usu_cedula', $cedula)
            ->first();
        if ($user) {
            echo "  $conn: ENCONTRADO - cedula=[{$user->usu_cedula}] nombre=[{$user->usu_nombre}]\n";
            echo "         hash={$user->usu_clave}\n";
            echo "         verify: " . (password_verify($password, trim($user->usu_clave ?? '')) ? 'PASA' : 'FALLA') . "\n";
        } else {
            echo "  $conn: NO ENCONTRADO\n";
        }
    } catch (\Throwable $e) {
        echo "  $conn: ERROR - " . $e->getMessage() . "\n";
    }
}

echo "\n=== 3. PROBAR CON DIFERENTES CEDULAS ===\n";
$cedulas = ['13354832', 'V13354832', 'v13354832', ' E-13354832'];
foreach ($cedulas as $c) {
    try {
        $user = DB::connection('simulacion')
            ->table('usuario')
            ->whereRaw('TRIM(usu_cedula) = ?', [trim($c)])
            ->first();
        echo "  Buscando '{$c}': " . ($user ? 'ENCONTRADO' : 'NO ENCONTRADO') . "\n";
    } catch (\Throwable $e) {
        echo "  Buscando '{$c}': ERROR - " . $e->getMessage() . "\n";
    }
}

echo "\n=== 4. LISTAR PRIMEROS 10 USUARIOS ===\n";
try {
    $users = DB::connection('simulacion')
        ->table('usuario')
        ->select(['usu_cedula', 'usu_nombre'])
        ->limit(10)
        ->get();
    echo "  Usuarios en simulacion:\n";
    foreach ($users as $u) {
        echo "    cedula=[{$u->usu_cedula}] nombre=[{$u->usu_nombre}]\n";
    }
} catch (\Throwable $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== 5. SIMULAR FLUJO COMPLETO DE LOGIN ===\n";
try {
    $conn = DbHelper::connection();
    echo "  Connection: $conn\n";

    $inputTrim = trim($cedula);
    
    // Step 1: Query simulation
    $extUser = DB::connection('simulacion')
        ->table('usuario')
        ->where(function ($q) use ($inputTrim) {
            $q->whereRaw('TRIM(usu_nombre) = ?', [$inputTrim])
              ->orWhereRaw('TRIM(usu_cedula) = ?', [$inputTrim]);
        })
        ->select(['usu_cedula', 'usu_nombre', 'usu_clave'])
        ->first();

    if ($extUser) {
        echo "  extUser encontrado en simulacion\n";
        $dbHash = trim($extUser->usu_clave ?? '');
        echo "  hash: {$dbHash}\n";
        
        $passIsValid = false;
        if (str_starts_with($dbHash, '$2')) {
            if (password_verify($password, $dbHash) || password_verify(strtoupper($password), $dbHash)) {
                $passIsValid = true;
            }
        }
        echo "  password_verify: " . ($passIsValid ? 'PASA' : 'FALLA') . "\n";
        
        // My fallback code
        if (!$passIsValid && DbHelper::intranetAvailable()) {
            echo "  Intentando fallback en intranet...\n";
            $intranetUser = DB::connection('intranet')
                ->table('usuario')
                ->whereRaw('TRIM(usu_cedula) = ?', [trim($extUser->usu_cedula)])
                ->select(['usu_cedula', 'usu_nombre', 'usu_clave'])
                ->first();
            if ($intranetUser) {
                echo "  intranet user encontrado\n";
                $dbHash = trim($intranetUser->usu_clave ?? '');
                if (str_starts_with($dbHash, '$2')) {
                    if (password_verify($password, $dbHash) || password_verify(strtoupper($password), $dbHash)) {
                        $passIsValid = true;
                        echo "  password_verify contra intranet: PASA\n";
                    }
                }
            }
        }
        
        echo "  Resultado final: " . ($passIsValid ? 'LOGIN EXITOSO' : 'LOGIN FALLIDO') . "\n";
    } else {
        echo "  extUser NO encontrado\n";
    }
} catch (\Throwable $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    echo "  Trace: " . $e->getTraceAsString() . "\n";
}
