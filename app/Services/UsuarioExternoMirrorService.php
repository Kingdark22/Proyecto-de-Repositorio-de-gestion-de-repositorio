<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Espeja usuarios externos (tabla 'usuarios_externos' en repositorio/pgsql)
 * hacia la tabla 'usuario' en la base de simulación.
 *
 * Los usuarios externos no tienen una cédula real (usu_cedula) como los usuarios
 * académicos de intranet. Se les asigna una cédula sintética con el formato:
 *   EXT-{uex_codigo}
 * Ejemplo: EXT-1, EXT-42
 */
class UsuarioExternoMirrorService
{
    protected string $simConnection = 'simulacion';

    protected string $repositorioConnection = 'pgsql';

    /**
     * Busca un usuario externo por nombre de usuario en la tabla usuarios_externos (repositorio).
     *
     * @return object|null
     */
    public function buscarExterno(string $input): ?object
    {
        try {
            return DB::connection($this->repositorioConnection)
                ->table('usuarios_externos')
                ->whereRaw('TRIM(uex_nombre) = ?', [trim($input)])
                ->first();
        } catch (\Throwable $e) {
            Log::warning('Error buscando usuario externo: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica la contraseña de un usuario externo.
     * Soporta bcrypt y legacy sha1(md5) igual que el login académico.
     */
    public function verificarPassword(string $password, string $storedHash): bool
    {
        $passTrim = trim($password);
        $dbHash = trim($storedHash);

        if ($dbHash === '') {
            return false;
        }

        // Bcrypt
        if (str_starts_with($dbHash, '$2')) {
            if (password_verify($passTrim, $dbHash) || password_verify(strtoupper($passTrim), $dbHash)) {
                return true;
            }
        } else {
            // Legacy sha1(md5)
            $legacyHash = sha1(md5($passTrim));
            $legacyHashUpper = sha1(md5(strtoupper($passTrim)));
            if (hash_equals($dbHash, $legacyHash) || hash_equals($dbHash, $legacyHashUpper)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Genera una cédula sintética para un usuario externo.
     * Formato: EXT-{uex_codigo}
     */
    public function generarCedulaSintetica(int $uexCodigo): string
    {
        return 'EXT-' . $uexCodigo;
    }

    /**
     * Genera un nombre de usuario basado en uex_nombre.
     * Si el nombre está vacío, usa 'EXT-{uex_codigo}'.
     */
    public function generarNombreUsuario(object $externo): string
    {
        $nombre = trim($externo->uex_nombre ?? '');
        if ($nombre === '') {
            return 'EXT-' . $externo->uex_codigo;
        }
        return $nombre;
    }

    /**
     * Espeja (crea o actualiza) un usuario externo en la tabla 'usuario' de simulación.
     *
     * @param object $externo Fila de usuarios_externos
     * @param string $password Plano (para hashear si es necesario)
     * @return string|null La cédula sintética generada, o null si falló
     */
    public function mirrorToSimulation(object $externo, string $password): ?string
    {
        if (! $this->simulacionTieneTablaUsuario()) {
            Log::warning('No se puede espejar usuario externo: simulacion no tiene tabla usuario');
            return null;
        }

        $syntheticCedula = $this->generarCedulaSintetica((int) $externo->uex_codigo);
        $nombre = $this->generarNombreUsuario($externo);
        $estatus = $this->mapearEstatus($externo->uex_estado ?? null);

        try {
            // Verificar si ya existe en simulación
            $existing = User::on($this->simConnection)
                ->whereRaw('TRIM(usu_cedula) = ?', [$syntheticCedula])
                ->first();

            if ($existing) {
                // Actualizar contraseña y nombre
                User::on($this->simConnection)
                    ->whereRaw('TRIM(usu_cedula) = ?', [$syntheticCedula])
                    ->update([
                        'usu_clave' => bcrypt($password),
                        'usu_nombre' => $nombre,
                        'usu_estatus' => $estatus,
                    ]);
                Log::info("Usuario externo {$syntheticCedula} actualizado en simulación");
            } else {
                // Crear nuevo registro
                User::on($this->simConnection)->insert([
                    'usu_cedula' => $syntheticCedula,
                    'usu_nombre' => $nombre,
                    'usu_clave' => bcrypt($password),
                    'usu_estatus' => $estatus,
                ]);
                Log::info("Usuario externo {$syntheticCedula} creado en simulación");
            }

            return $syntheticCedula;
        } catch (\Throwable $e) {
            Log::warning("Error espejando usuario externo {$syntheticCedula}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica si el usuario externo está activo.
     */
    public function isActivo(object $externo): bool
    {
        return ! in_array(trim((string) ($externo->uex_estado ?? '')), ['I', '0', 'INACTIVO', 'false', 'False'], true);
    }

    /**
     * Mapea el estado del usuario externo al formato de usu_estatus.
     */
    protected function mapearEstatus($estado): string
    {
        $estado = trim((string) ($estado ?? 'A'));
        if (in_array($estado, ['I', '0', 'INACTIVO', 'false', 'False'], true)) {
            return 'I';
        }
        return 'A';
    }

    /**
     * Verifica si la tabla 'usuario' existe en simulación.
     */
    protected function simulacionTieneTablaUsuario(): bool
    {
        try {
            return Schema::connection($this->simConnection)->hasTable('usuario');
        } catch (\Throwable) {
            return false;
        }
    }
}
