<?php

namespace App\Livewire;

use App\Helpers\DbHelper;
use App\Models\User;
use App\Services\IntranetSimulationMirrorService;
use App\Services\UserRoleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Login extends Component
{
    public string $usuario = '';
    public string $password = '';
    public string $error = '';
    public bool $cargando = false;

    public function login()
    {
        $this->error = '';
        $this->cargando = true;

        $inputTrim  = trim($this->usuario);
        $passTrim   = trim($this->password);

        if (empty($inputTrim) || empty($passTrim)) {
            $this->error = 'Por favor ingrese su usuario y contraseña.';
            $this->cargando = false;
            return;
        }

        try {
            $cedula = '';
            $extUser = null;
            $fromIntranet = false;

            // 1. Buscar en SIMULACIÓN primero (rápido, sin red)
            try {
                $extUser = \Illuminate\Support\Facades\DB::connection('simulacion')
                    ->table('usuario')
                    ->where(function ($q) use ($inputTrim) {
                        $q->whereRaw('TRIM(usu_nombre) = ?', [$inputTrim])
                          ->orWhereRaw('TRIM(usu_cedula) = ?', [$inputTrim]);
                    })
                    ->select(['usu_cedula', 'usu_nombre', 'usu_clave'])
                    ->first();
            } catch (\Throwable $simError) {
                Log::warning('Error consultando simulación para login: ' . $simError->getMessage());
            }

            // 2. Si no está en simulación e intranet disponible, buscar en intranet
            if (! $extUser && DbHelper::intranetAvailable()) {
                try {
                    $extUser = \Illuminate\Support\Facades\DB::connection('intranet')
                        ->table('usuario')
                        ->where(function ($q) use ($inputTrim) {
                            $q->whereRaw('TRIM(usu_nombre) = ?', [$inputTrim])
                              ->orWhereRaw('TRIM(usu_cedula) = ?', [$inputTrim]);
                        })
                        ->select(['usu_cedula', 'usu_nombre', 'usu_clave'])
                        ->first();
                    $fromIntranet = true;
                } catch (\Throwable $intError) {
                    Log::warning('Error consultando intranet para login: ' . $intError->getMessage());
                }
            }

            if (! $extUser) {
                $this->error = 'Usuario o contraseña incorrectos.';
                $this->cargando = false;
                return;
            }

            $dbHash = trim($extUser->usu_clave ?? '');
            $passIsValid = false;

            if (str_starts_with($dbHash, '$2')) {
                // Bcrypt hash
                if (password_verify($passTrim, $dbHash) || password_verify(strtoupper($passTrim), $dbHash)) {
                    $passIsValid = true;
                }
            } else {
                // Legacy hashes: sha1(md5($password)) or sha1(md5(strtoupper($password)))
                $legacyHash = sha1(md5($passTrim));
                $legacyHashUpper = sha1(md5(strtoupper($passTrim)));
                if (hash_equals($dbHash, $legacyHash) || hash_equals($dbHash, $legacyHashUpper)) {
                    $passIsValid = true;
                }
            }

            if (! $passIsValid) {
                $this->error = 'Usuario o contraseña incorrectos.';
                $this->cargando = false;
                return;
            }

            $cedula = trim($extUser->usu_cedula);

            // 3. Si vino de intranet, espejar PRIMERO antes de buscar el modelo
            if ($fromIntranet) {
                try {
                    app(IntranetSimulationMirrorService::class)->mirrorUserContext($cedula);
                } catch (\Throwable $e) {
                    Log::warning('Error espejando usuario a simulación: ' . $e->getMessage());
                }
            }

            // 4. Buscar el modelo User (siempre en simulación después del mirroring)
            $user = User::on('simulacion')->whereRaw('TRIM(usu_cedula) = ?', [$cedula])->first();

            // 5. Si vino de intranet pero el mirroring no copió el usuario, caer en intranet
            if (! $user && $fromIntranet) {
                $user = User::on('intranet')->whereRaw('TRIM(usu_cedula) = ?', [$cedula])->first();
            }

            if (! $user) {
                $this->error = 'No se pudo cargar el usuario. Intente de nuevo.';
                $this->cargando = false;
                return;
            }

            Auth::login($user);
            request()->session()->regenerate();

            $roleService = app(UserRoleService::class);
            $roleService->bootstrapSessionRole($user);

            if ($roleService->getActiveRole($user) === null) {
                return $this->redirect(route('acceso-rol.index'), navigate: false);
            }

            return $this->redirect(route('dashboard'), navigate: false);

        } catch (\Throwable $e) {
            Log::error('Login error: ' . $e->getMessage());

            $msg = $e->getMessage();
            if (str_contains($msg, 'timeout expired') || str_contains($msg, '08006') || str_contains($msg, 'could not connect') || str_contains($msg, 'connection refused')) {
                $this->error = 'El sistema de intranet no está disponible en este momento. Se está usando la base de datos de respaldo. Intente de nuevo o contacte al administrador.';
            } else {
                $this->error = 'Error de conexión. Por favor intente de nuevo.';
            }
            $this->cargando = false;
        }
    }

    public function render()
    {
        return view('livewire.login');
    }
}
