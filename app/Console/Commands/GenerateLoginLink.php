<?php

namespace App\Console\Commands;

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

        try {
            // Forzar conexión a la intranet (PostgreSQL) — no usar fallback a simulación
            $connectionName = 'intranet';

            $extUser = DB::connection($connectionName)
                ->table('usuario')
                ->leftJoin('persona', DB::raw('TRIM(usuario.usu_cedula)'), '=', DB::raw('TRIM(persona.per_cedula)'))
                ->where(function($q) use ($input) {
                    $inputTrim = trim($input);
                    $q->where(DB::raw('TRIM(usuario.usu_nombre)'), $inputTrim)
                      ->orWhere(DB::raw('TRIM(usuario.usu_cedula)'), $inputTrim);
                })
                ->select(['usuario.usu_cedula', 'usuario.usu_nombre', 'usuario.usu_clave', 'persona.per_nombres', 'persona.per_apellidos'])
                ->first();

            if (!$extUser) {
                $this->error('Usuario no encontrado en la intranet.');
                return 1;
            }

            $cedula = trim($extUser->usu_cedula);
            
            // Exportar información consultada de inmediato a la BD de simulación
            if (!ini_get('safe_mode')) set_time_limit(300);
            $mirror = app(\App\Services\IntranetSimulationMirrorService::class);
            $mirror->mirrorUserContext($cedula);
            $mirror->mirrorTable('programa');

            $nombre = trim($extUser->per_nombres ?? '') . ' ' . trim($extUser->per_apellidos ?? '');

            $timestamp = time();
            $firma = hash('sha256', $cedula . $timestamp . config('app.sogac_key', 'RXN0ZUVzVW5TZWNyZXRvRGUzMkJ5dGVzRXhhY3Rvc3M='));

            $payload = [
                'cedula' => $cedula,
                'nombre' => $nombre,
                'fecha_creacion' => $timestamp,
                'firma_validacion' => $firma,
                'timestamp' => $timestamp,
            ];

            $ticket = $this->encryptPayload($payload);
            $baseUrl = config('app.url');
            if (empty($baseUrl) || $baseUrl === 'http://localhost') {
                $baseUrl = 'http://localhost:8000';
            }
            $url = rtrim($baseUrl, '/') . '/login?payload=' . urlencode($ticket);

            $this->line('');
            $this->info('¡Enlace generado exitosamente!');
            $this->line('');
            $this->line('<fg=cyan>' . $url . '</>');
            $this->line('');
            
            return 0;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
