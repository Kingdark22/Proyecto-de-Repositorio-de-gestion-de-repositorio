<?php

namespace App\Console\Commands;

use App\Helpers\DbHelper;
use App\Helpers\DualDatabase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateLoginLink extends Command
{
    protected $signature = 'app:generate-login-link {user? : El usuario o cédula}';
    protected $description = 'Genera un enlace de acceso directo verificando el usuario/cédula con la BD externa (sin requerir contraseña).';

    /**
     * Clave secreta para encriptar el payload del enlace.
     */
    protected function getKey(): string
    {
        return base64_decode(config('app.sogac_key', 'RXN0ZUVzVW5TZWNyZXRvRGUzMkJ5dGVzRXhhY3Rvc3M='));
    }

    /**
     * Encripta datos en formato compatible con Crypt de Laravel.
     */
    protected function encryptPayload(array $data): string
    {
        $key = $this->getKey();
        $iv = openssl_random_pseudo_bytes(16);

        $value = json_encode($data);
        $encryptedValue = openssl_encrypt($value, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        $iv_b64 = base64_encode($iv);
        $value_b64 = base64_encode($encryptedValue);

        $mac = hash_hmac('sha256', $iv_b64 . $value_b64, $key);

        $json_payload = json_encode([
            'iv' => $iv_b64,
            'value' => $value_b64,
            'mac' => $mac,
            'tag' => ''
        ]);

        return base64_encode($json_payload);
    }

    protected function buscarUsuarios(string $cedula): array
    {
        $usuarios = [];
        $vistos = [];

        foreach (['simulacion', 'intranet'] as $conn) {
            if ($conn === 'intranet' && !config('database.connections.intranet.enabled', true)) {
                continue;
            }
            try {
                $rows = DB::connection($conn)
                    ->table('usuario')
                    ->leftJoin('persona', DB::raw('TRIM(usuario.usu_cedula)'), '=', DB::raw('TRIM(persona.per_cedula)'))
                    ->where(DB::raw('TRIM(usuario.usu_cedula)'), $cedula)
                    ->where('usuario.usu_estatus', 'A')
                    ->select(['usuario.usu_cedula', 'usuario.usu_nombre', 'persona.per_nombres', 'persona.per_apellidos'])
                    ->get();

                foreach ($rows as $r) {
                    $key = trim($r->usu_nombre ?? '');
                    if ($key === '' || isset($vistos[$key])) continue;
                    $vistos[$key] = true;
                    $usuarios[] = [
                        'cedula' => trim($r->usu_cedula),
                        'usu_nombre' => $key,
                        'nombre_completo' => trim($r->per_nombres ?? '') . ' ' . trim($r->per_apellidos ?? ''),
                        'fuente' => $conn,
                    ];
                }
            } catch (\Throwable $e) {
                if ($conn === 'intranet') {
                    $this->warn('Intranet no disponible, usando solo simulación.');
                }
            }
        }

        return $usuarios;
    }

    public function handle()
    {
        $this->info('--- Generador de Enlace ---');

        $input = $this->argument('user');

        if (empty($input)) {
            fwrite(STDOUT, "Usuario / Cédula: ");
            $input = trim(fgets(STDIN));
        }

        if (empty($input)) {
            $this->error('Incompleto.');
            return 1;
        }

        $input = trim($input);

        try {
            // Buscar por cédula o nombre de usuario
            $usuarios = [];
            $cedula = $input;

            if (is_numeric($input)) {
                $usuarios = $this->buscarUsuarios($input);
            } else {
                // Buscar por nombre de usuario — obtener la cédula primero
                foreach (['simulacion', 'intranet'] as $conn) {
                    if ($conn === 'intranet' && !config('database.connections.intranet.enabled', true)) continue;
                    try {
                        $u = DB::connection($conn)->table('usuario')
                            ->where(DB::raw('TRIM(usu_nombre)'), $input)
                            ->first(['usu_cedula']);
                        if ($u) {
                            $cedula = trim($u->usu_cedula);
                            $usuarios = $this->buscarUsuarios($cedula);
                            break;
                        }
                    } catch (\Throwable $e) {}
                }
            }

            if (empty($usuarios)) {
                $this->error('No se encontraron usuarios para: ' . $input);
                return 1;
            }

            $cedula = $usuarios[0]['cedula'];
            $nombreCompleto = $usuarios[0]['nombre_completo'];

            // Exportar contexto a simulación
            if (!ini_get('safe_mode')) set_time_limit(300);
            try {
                $mirror = app(\App\Services\IntranetSimulationMirrorService::class);
                $mirror->mirrorUserContext($cedula);
                $mirror->mirrorTable('programa');
            } catch (\Throwable $e) {
                $this->warn('No se pudo exportar a simulación (no crítico): ' . $e->getMessage());
            }

            $this->line('');
            $this->info('Persona: ' . $nombreCompleto . ' (C.I. ' . $cedula . ')');
            $this->line('');

            // Mostrar usuarios disponibles
            $this->info('Usuarios encontrados en la base de datos:');
            $this->line('');

            $roleService = app(\App\Services\UserRoleService::class);
            $idxUsuario = 1;
            $userMap = [];

            foreach ($usuarios as $u) {
                $label = "[" . $idxUsuario . "] " . $u['usu_nombre'] . '  (' . $u['fuente'] . ')';

                // Detectar roles para este usuario
                $tempUser = new \App\Models\User();
                $tempUser->usu_cedula = $u['cedula'];
                try {
                    $roles = $roleService->detectAvailableRoles($tempUser);
                    if (!empty($roles)) {
                        $label .= '  → ' . implode(', ', array_values($roles));
                    } else {
                        $label .= '  → (sin roles detectados)';
                    }
                } catch (\Throwable $e) {
                    $label .= '  → (error detectando roles)';
                }

                $this->line($label);
                $userMap[$idxUsuario] = $u;
                $idxUsuario++;
            }

            $this->line('  [0] Cancelar');
            $this->line('');

            fwrite(STDOUT, "Selecciona el usuario (0-" . ($idxUsuario - 1) . "): ");
            $userChoice = trim(fgets(STDIN));

            if ($userChoice === '' || (int) $userChoice === 0 || !isset($userMap[(int) $userChoice])) {
                $this->warn('Operación cancelada.');
                return 1;
            }

            $selectedUser = $userMap[(int) $userChoice];
            $selectedUsuNombre = $selectedUser['usu_nombre'];
            $this->info('Usuario seleccionado: ' . $selectedUsuNombre);
            $this->line('');

            // Detectar roles para el usuario seleccionado
            $preRole = null;
            try {
                $tempUser = new \App\Models\User();
                $tempUser->usu_cedula = $cedula;
                $availableRoles = $roleService->detectAvailableRoles($tempUser);

                if (count($availableRoles) > 0) {
                    $this->info('Roles disponibles para ' . $selectedUsuNombre . ':');
                    $this->line('');

                    $index = 1;
                    $roleMap = [];
                    foreach ($availableRoles as $slug => $label) {
                        $this->line("  [$index] $label");
                        $roleMap[$index] = $slug;
                        $index++;
                    }

                    $this->line('  [0] Ninguno (seleccionar después en el navegador)');
                    $this->line('');

                    fwrite(STDOUT, "Selecciona el rol (0-" . ($index - 1) . "): ");
                    $choice = trim(fgets(STDIN));

                    if ($choice !== '' && isset($roleMap[(int) $choice])) {
                        $preRole = $roleMap[(int) $choice];
                        $this->info('Rol seleccionado: ' . $availableRoles[$preRole]);
                    } else {
                        $this->warn('No se seleccionó rol — se elegirá en el navegador.');
                    }
                } else {
                    $this->warn('No se detectaron roles para esta cédula.');
                }
            } catch (\Throwable $e) {
                $this->warn('No se pudieron detectar roles (no crítico): ' . $e->getMessage());
            }

            $timestamp = time();
            $firma = hash('sha256', $cedula . $timestamp . config('app.sogac_key', 'RXN0ZUVzVW5TZWNyZXRvRGUzMkJ5dGVzRXhhY3Rvc3M='));

            $payload = [
                'cedula' => $cedula,
                'usu_nombre' => $selectedUsuNombre,
                'nombre' => $nombreCompleto,
                'fecha_creacion' => $timestamp,
                'firma_validacion' => $firma,
                'timestamp' => $timestamp,
            ];

            if ($preRole) {
                $payload['pre_role'] = $preRole;
            }

            $ticket = $this->encryptPayload($payload);
            $baseUrl = config('app.url');
            if (empty($baseUrl) || $baseUrl === 'http://localhost') {
                $baseUrl = 'http://localhost:8000';
            }
            $url = rtrim($baseUrl, '/') . '/login?payload=' . urlencode($ticket);

            $this->line('');
            $this->info('¡Enlace generado exitosamente!');
            $this->line('');
            $this->line('Abre esta URL en el navegador:');
            $this->line('<fg=cyan>' . $url . '</>');
            $this->line('');
            $this->warn('Asegúrate de que el servidor web esté corriendo en: ' . $baseUrl);

            return 0;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
