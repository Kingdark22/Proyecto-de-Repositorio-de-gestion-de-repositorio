<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DbHelper
{
    protected static ?string $connectionName = null;

    protected static bool $resolved = false;

    protected static bool $usingIntranet = false;

    protected static ?string $intranetHost = null;

    protected static ?int $intranetPort = null;

    public static function connection()
    {
        if (self::$resolved && self::$connectionName !== null) {
            return self::$connectionName;
        }

        if (!config('database.connections.intranet.enabled', true)) {
            self::$connectionName = 'simulacion';
            self::$usingIntranet = false;
            self::$resolved = true;
            return self::$connectionName;
        }

        // Socket check rápido (50ms) — si falla, ni tocamos PDO
        if (!self::intranetAlcanzable()) {
            self::$connectionName = 'simulacion';
            self::$usingIntranet = false;
            self::$resolved = true;
            return self::$connectionName;
        }

        try {
            $pdo = DB::connection('intranet')->getPdo();
            $pdo->query('SELECT 1')->fetch();
            self::$connectionName = 'intranet';
            self::$usingIntranet = true;
            try {
                $pdo->exec('SET statement_timeout = 500');
            } catch (\Exception $e) {
                Log::warning('No se pudo ajustar statement_timeout (no crítico): ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::warning('Intranet no disponible (PDO/query): ' . $e->getMessage());
            self::$connectionName = 'simulacion';
            self::$usingIntranet = false;
        }

        self::$resolved = true;

        return self::$connectionName;
    }

    /**
     * Si una query falla en medio del request, resetea para que la siguiente
     * llamada vuelva a evaluar la conexión.
     */
    public static function handleQueryError(\Exception $e): void
    {
        $msg = $e->getMessage();
        if (str_contains($msg, 'timeout expired') || str_contains($msg, '08006') || str_contains($msg, 'could not connect') || str_contains($msg, 'connection refused') || str_contains($msg, '08001')) {
            if (self::$usingIntranet) {
                Log::warning('Intranet query falló, cambiando a simulación: ' . $msg);
                self::reset();
            }
        }
    }

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
        $fp = @fsockopen(self::$intranetHost, self::$intranetPort, $errno, $errstr, 0.05);

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

    /**
     * Verifica si la intranet está realmente disponible (conexión directa).
     */
    public static function intranetAvailable(): bool
    {
        try {
            if (!config('database.connections.intranet.enabled', true)) {
                return false;
            }

            if (!self::intranetAlcanzable()) {
                return false;
            }

            $pdo = DB::connection('intranet')->getPdo();
            $pdo->query('SELECT 1')->fetch();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function reset(): void
    {
        self::$connectionName = null;
        self::$resolved = false;
        self::$usingIntranet = false;
        self::$intranetHost = null;
        self::$intranetPort = null;
    }
}
