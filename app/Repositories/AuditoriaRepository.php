<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditoriaRepository
{
    protected function conexionRepositorio(): string
    {
        return (string) config('dual_database.repositorio_connection', 'pgsql');
    }

    public function tablaExiste(): bool
    {
        return Schema::connection($this->conexionRepositorio())->hasTable('auditorias');
    }

    public function registrar(int $proyectoId, string $accion, string $ip = '', string $userAgent = ''): ?int
    {
        if (!$this->tablaExiste()) {
            return null;
        }

        try {
            return DB::connection($this->conexionRepositorio())->table('auditorias')->insertGetId([
                'pry_codigo' => $proyectoId,
                'aud_accion' => $accion,
                'aud_modulo' => 'proyectos',
                'ip' => $ip,
                'aud_user_agent' => substr($userAgent, 0, 500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            return null;
        }
    }

    public function actualizarProyecto(int $proyectoId, int $audId): void
    {
        try {
            DB::connection($this->conexionRepositorio())
                ->table('proyectos')
                ->where('pry_codigo', $proyectoId)
                ->update(['aud_codigo' => $audId]);
        } catch (\Throwable) {
            // No bloquear si falla
        }
    }
}
