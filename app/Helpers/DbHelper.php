<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DbHelper
{
    protected static ?string $connectionName = null;

    protected static bool $resolved = false;

    protected static bool $usingIntranet = false;

    protected static ?string $intranetHost = null;

    protected static ?int $intranetPort = null;

    protected const CACHE_TTL = 60;

    protected const CACHE_KEY = 'dbhelper_intranet_available';

    /**
     * Retorna el nombre de la conexión activa. Si la intranet está caída, retorna 'simulacion' como fallback.
     */
    public static function connection()
    {
        if (self::$resolved && self::$connectionName !== null) {
            return self::$connectionName;
        }

        if (!self::intranetAlcanzable()) {
            self::$connectionName = 'simulacion';
            self::$usingIntranet = false;
            self::$resolved = true;
            Cache::forget(self::CACHE_KEY);
            return self::$connectionName;
        }

        $cached = Cache::get(self::CACHE_KEY);
        if ($cached === 'intranet') {
            self::$connectionName = 'intranet';
            self::$usingIntranet = true;
            self::$resolved = true;
            return self::$connectionName;
        }

        try {
            $pdo = DB::connection('intranet')->getPdo();
            $pdo->query('SELECT 1')->fetch();
            self::$connectionName = 'intranet';
            self::$usingIntranet = true;
            Cache::put(self::CACHE_KEY, 'intranet', now()->addSeconds(self::CACHE_TTL));
            try {
                $pdo->exec('SET statement_timeout = 1000');
            } catch (\Exception $e) {
                Log::warning('No se pudo ajustar statement_timeout (no crítico): ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::warning('Intranet no disponible (PDO/query): ' . $e->getMessage());
            self::$connectionName = 'simulacion';
            self::$usingIntranet = false;
            Cache::forget(self::CACHE_KEY);
        }

        self::$resolved = true;

        return self::$connectionName;
    }

    /**
     * Verifica si un error de base de datos es por timeout/caída de intranet.
     * Si es así, resetea el cache para que la siguiente petición pruebe simulación.
     */
    public static function handleQueryError(\Exception $e): void
    {
        $msg = $e->getMessage();
        if (str_contains($msg, 'timeout expired') || str_contains($msg, '08006') || str_contains($msg, 'could not connect')) {
            if (self::$usingIntranet || Cache::get(self::CACHE_KEY) === 'intranet') {
                Log::warning('Intranet query falló, cambiando a simulación: ' . $msg);
                self::reset();
            }
        }
    }

    /**
     * Verifica si el host:puerto de intranet es alcanzable con un socket rápido (300ms).
     */
    protected static function intranetAlcanzable(): bool
    {
        if (self::$intranetHost === null) {
            self::$intranetHost = (string) config('database.connections.intranet.host', '');
            self::$intranetPort = (int) config('database.connections.intranet.port', 5432);
        }

        if (self::$intranetHost === '' || self::$intranetPort <= 0) {
            return false;
        }

        $errno = null;
        $errstr = '';
        $fp = @fsockopen(self::$intranetHost, self::$intranetPort, $errno, $errstr, 0.3);

        if ($fp) {
            fclose($fp);
            return true;
        }

        return false;
    }

    public static function isUsingIntranet(): bool
    {
        self::connection();

        return self::$usingIntranet;
    }

    public static function reset(): void
    {
        self::$connectionName = null;
        self::$resolved = false;
        self::$usingIntranet = false;
        self::$intranetHost = null;
        self::$intranetPort = null;
        Cache::forget(self::CACHE_KEY);
    }
}
