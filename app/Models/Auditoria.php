<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Auditoria extends Model
{
    protected $table = 'auditorias';
    protected $primaryKey = 'aud_codigo';
    protected $fillable = ['pry_codigo', 'aud_accion', 'aud_modulo', 'ip', 'aud_user_agent'];

    public function user()
    {
        return $this->belongsTo(User::class, 'persona_id');
    }

    /**
     * Registra una auditoría para un proyecto y actualiza el proyecto con el ID de auditoría.
     */
    public static function registrarParaProyecto(int $proyectoId, string $accion, string $modulo = 'proyectos', ?string $connection = null): void
    {
        $connection = $connection ?? config('dual_database.repositorio_connection', 'pgsql');

        if (! Schema::connection($connection)->hasTable('auditorias')) {
            return;
        }

        try {
            $audId = DB::connection($connection)->table('auditorias')->insertGetId([
                'pry_codigo' => $proyectoId,
                'aud_accion' => $accion,
                'aud_modulo' => $modulo,
                'ip' => request()->ip(),
                'aud_user_agent' => substr((string) request()->userAgent(), 0, 500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::connection($connection)
                ->table('proyectos')
                ->where('pry_codigo', $proyectoId)
                ->update(['aud_codigo' => $audId]);
        } catch (\Throwable) {
            // No bloquear el registro si falla la auditoría
        }
    }
}
