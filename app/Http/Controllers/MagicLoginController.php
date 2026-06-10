<?php

namespace App\Http\Controllers;

use App\Helpers\DbHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\IntranetSimulationMirrorService;
use App\Services\UserRoleService;

class MagicLoginController extends Controller
{
    /**
     * Clave secreta para desencriptar el payload.
     */
    protected function getKey(): string
    {
        return base64_decode(config('app.sogac_key', 'RXN0ZUVzVW5TZWNyZXRvRGUzMkJ5dGVzRXhhY3Rvc3M='));
    }

    /**
     * Desencripta el payload del enlace.
     */
    protected function decryptPayload(string $ticket): ?array
    {
        $key = $this->getKey();

        $json_payload = base64_decode($ticket);
        if (!$json_payload) return null;

        $envelope = json_decode($json_payload, true);
        if (!$envelope || !isset($envelope['iv'], $envelope['value'], $envelope['mac'])) return null;

        // Verificar MAC (integridad)
        $expectedMac = hash_hmac('sha256', $envelope['iv'] . $envelope['value'], $key);
        if (!hash_equals($expectedMac, $envelope['mac'])) return null;

        // Desencriptar
        $iv = base64_decode($envelope['iv']);
        $encryptedValue = base64_decode($envelope['value']);
        $decrypted = openssl_decrypt($encryptedValue, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) return null;

        $data = json_decode($decrypted, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Procesa el login mediante enlace encriptado.
     */
    public function login(Request $request)
    {
        $ticket = $request->query('token') ?? $request->query('payload');

        if (empty($ticket)) {
            return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                <h1 style="color:#c00;">Acceso Denegado</h1>
                <p>No se proporcion? un enlace de acceso v?lido.</p>
            </body></html>', 403);
        }

        // 1. Desencriptar payload
        $payload = $this->decryptPayload($ticket);

        if (!$payload || !isset($payload['cedula'], $payload['fecha_creacion'], $payload['firma_validacion'])) {
            return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                <h1 style="color:#c00;">Enlace Inv?lido</h1>
                <p>El enlace de acceso no es v?lido o fue manipulado.</p>
            </body></html>', 403);
        }

        // 2. Verificar firma
        $keyB64 = config('app.sogac_key', 'RXN0ZUVzVW5TZWNyZXRvRGUzMkJ5dGVzRXhhY3Rvc3M=');
        $seed = $payload['cedula'] . $payload['fecha_creacion'] . $keyB64;
        $firmaEsperada = hash('sha256', $seed);

        if (!hash_equals($firmaEsperada, $payload['firma_validacion'])) {
            return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                <h1 style="color:#c00;">Enlace Inv?lido</h1>
                <p>La firma del enlace no es v?lida.</p>
            </body></html>', 403);
        }

        // 3. Verificar expiraci?n del enlace (por defecto 1 d?a; la sesi?n web es independiente y m?s larga)
        $ttl = (int) config('app.magic_link_ttl', 86400);
        $elapsed = time() - (int) $payload['fecha_creacion'];
        if ($elapsed > $ttl || $elapsed < 0) {
            $horas = (int) round($ttl / 3600);
            return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                <h1 style="color:#c00;">Enlace Expirado</h1>
                <p>Este enlace de acceso ha expirado (v?lido por ' . $horas . ' horas). Genere uno nuevo desde la terminal.</p>
            </body></html>', 403);
        }

        $cedula = trim($payload['cedula']);

        try {
            // 4. Buscar usuario en BD externa con fallback automático a simulación.
            $connection = DbHelper::connection();
            $user = null;

            try {
                $user = User::on($connection)->whereRaw('TRIM(usu_cedula) = ?', [$cedula])->first();
            } catch (\Exception $e) {
                DbHelper::handleQueryError($e);
                if ($connection === 'intranet') {
                    Log::warning('MagicLogin: intranet query falló, reintentando en simulación: ' . $e->getMessage());
                    $user = User::on('simulacion')->whereRaw('TRIM(usu_cedula) = ?', [$cedula])->first();
                }
            }

            if (! $user && $connection === 'intranet') {
                $user = User::on('simulacion')->whereRaw('TRIM(usu_cedula) = ?', [$cedula])->first();
            }

            if (!$user) {
                return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                    <h1 style="color:#c00;">Error</h1>
                    <p>El usuario no fue encontrado en la base de datos.</p>
                </body></html>', 404);
            }

            // 5. Login + regenerar sesión
            Auth::login($user);
            $request->session()->regenerate();
            Log::info('User authenticated: ' . (Auth::check() ? 'true' : 'false'));

            app(IntranetSimulationMirrorService::class)->mirrorUserContext($cedula);

            $roleService = app(UserRoleService::class);

            // 7. Aplicar pre-rol si viene en el payload, si no usar el predeterminado
            if (isset($payload['pre_role']) && $payload['pre_role']) {
                $roleService->setActiveRole($user, $payload['pre_role']);
            } else {
                $roleService->bootstrapSessionRole($user);
            }

            if ($roleService->getActiveRole($user) === null) {
                return redirect()->route('acceso-rol.index');
            }

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                <h1 style="color:#c00;">Error de Conexi?n</h1>
                <p>' . $e->getMessage() . '</p>
            </body></html>', 500);
        }
    }
}
