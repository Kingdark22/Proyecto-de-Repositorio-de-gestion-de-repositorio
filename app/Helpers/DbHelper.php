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
     * Conexión por defecto para TODAS las LECTURAS.
     *
     * Ahora retorna SIEMPRE 'simulacion' como predeterminado.
     * La intranet SOLO se usa para espejar datos en segundo plano (mirroring),
     * NUNCA para lecturas en línea. Esto elimina la oscilación entre conexiones
     * cuando el internet es lento o intermitente.
     */
    public static function connection(): string
    {
        if (self::$resolved && self::$connectionName !== null) {
            return self::$connectionName;
        }

        self::$connectionName = 'simulacion';
        self::$usingIntranet = self::intranetAlcanzable();
        self::$resolved = true;

        return self::$connectionName;
    }

    /**
     * Verifica si la intranet está disponible para MIRRORING (copia de datos en segundo plano).
     * NO afecta las lecturas del sistema — esas siempre van a 'simulacion'.
     */
    public static function intranetAvailable(): bool
    {
        return self::intranetAlcanzable();
    }

    /**
     * Retorna true si la intranet está alcanzable. Se usa para decidir
     * si se debe ejecutar mirroring (copia de datos intranet → simulación).
     */
    public static function isUsingIntranet(): bool
    {
        return self::intranetAlcanzable();
    }

    /**
     * Verifica si un error de base de datos es por timeout/caída de intranet.
     * Ya no cambia la conexión de lectura (siempre es 'simulacion'),
     * solo limpia el cache de disponibilidad.
     */
    public static function handleQueryError(\Exception $e): void
    {
        $msg = $e->getMessage();
        if (str_contains($msg, 'timeout expired') || str_contains($msg, '08006') || str_contains($msg, 'could not connect')) {
            Log::warning('Error de conexión a intranet detectado: ' . $msg);
            // Ya no se resetea la conexión — siempre usamos simulación para lecturas
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

        try {
            $errno = null;
            $errstr = '';
            $fp = @fsockopen(self::$intranetHost, self::$intranetPort, $errno, $errstr, 0.3);

            if ($fp) {
                fclose($fp);
                return true;
            }
        } catch (\Throwable) {
            // Silently fail - intranet not reachable
        }

        return false;
    }

    public static function reset(): void
    {
        self::$connectionName = null;
        self::$resolved = false;
        self::$intranetHost = null;
        self::$intranetPort = null;
    }
}

