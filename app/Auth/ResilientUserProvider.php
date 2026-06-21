<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ResilientUserProvider
 *
 * Extiende EloquentUserProvider para que las fallas de BD (timeout de intranet)
 * NO provoquen el cierre de sesión del usuario.
 *
 * Cuando Laravel autentica un usuario en cada request, llama retrieveById().
 * Si la BD externa (intranet) hace timeout, esa excepción se propagaba hasta el
 * middleware Authenticate y el usuario quedaba deslogueado.
 *
 * Este provider captura esos errores y devuelve el usuario desde el cache persistente
 * que ya se guarda en User::find() y Login.php.
 */
class ResilientUserProvider extends EloquentUserProvider
{
    /**
     * Recupera un usuario por su ID primario.
     * Si la BD falla o está lenta, intenta restaurar desde cache.
     * Prioriza el cache para evitar consultas a la BD en cada request.
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $identifier = is_string($identifier) ? trim($identifier) : $identifier;
        if ($identifier === null || $identifier === '') {
            Log::warning('ResilientUserProvider::retrieveById — identifier vacío');
            return null;
        }

        Log::debug('ResilientUserProvider::retrieveById — inicio para cedula: ' . $identifier);

        // 1. Cache persistente
        try {
            $cacheKey = 'user_find_persisted_' . trim((string) $identifier);
            $data = Cache::get($cacheKey);

            if (is_array($data) && isset($data['usu_cedula'])) {
                Log::debug('ResilientUserProvider::retrieveById — encontrado en cache');
                $instance = new User;
                $instance->setConnection(\App\Helpers\DbHelper::connection());
                $instance->setRawAttributes($data);
                $instance->exists = true;
                $instance->syncOriginal();
                return $instance;
            }
            Log::debug('ResilientUserProvider::retrieveById — cache miss para key: ' . $cacheKey);
        } catch (\Throwable $e) {
            Log::warning('ResilientUserProvider: cache fallback inicial falló: ' . $e->getMessage());
        }

        // 2. User::find con multi-conexión
        try {
            $user = User::find($identifier);
            if ($user !== null) {
                Log::debug('ResilientUserProvider::retrieveById — encontrado via User::find');
                return $user;
            }
            Log::warning('ResilientUserProvider::retrieveById — User::find devolvió NULL');
        } catch (\Throwable $e) {
            Log::warning('ResilientUserProvider::retrieveById — BD falló: ' . $e->getMessage());
        }

        // 3. Respaldo en la sesión
        try {
            $sessionBackup = \Illuminate\Support\Facades\Session::get('user_backup_data_' . trim((string) $identifier));
            if (is_array($sessionBackup) && isset($sessionBackup['usu_cedula'])) {
                Log::debug('ResilientUserProvider::retrieveById — encontrado en session backup');
                $instance = new User;
                $instance->setConnection(\App\Helpers\DbHelper::connection());
                $instance->setRawAttributes($sessionBackup);
                $instance->exists = true;
                $instance->syncOriginal();
                return $instance;
            }
        } catch (\Throwable $e) {
            Log::warning('ResilientUserProvider: session backup fallback falló: ' . $e->getMessage());
        }

        Log::warning('ResilientUserProvider::retrieveById — TODOS los fallbacks fallaron para: ' . $identifier);
        return null;
    }

    /**
     * Recupera un usuario por token de "remember me".
     * El modelo no usa remember tokens, pero igual protegemos contra BD caída.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        try {
            return parent::retrieveByToken($identifier, $token);
        } catch (\Throwable $e) {
            Log::warning('ResilientUserProvider::retrieveByToken — BD falló: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Recupera un usuario por las credenciales dadas.
     * Protegemos contra BD caída (aunque login maneja esto internamente en Login.php).
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        try {
            return parent::retrieveByCredentials($credentials);
        } catch (\Throwable $e) {
            Log::warning('ResilientUserProvider::retrieveByCredentials — BD falló: ' . $e->getMessage());
            return null;
        }
    }
}
