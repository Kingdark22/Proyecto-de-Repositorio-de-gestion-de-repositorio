<?php

namespace App\Support;

use App\Models\User;
use App\Services\IntranetEquipoSeccionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Consulta si un estudiante pertenece a un equipo de proyecto (intranet / simulación).
 * Líder/autor se filtran por código de rol de proyecto, sin usar el modelo Role.
 */
class UserEquiposQuery
{
    public const ROL_LIDER = 1;

    public const ROL_AUTOR = 2;

    protected ?int $rolProyectoId = null;

    public function __construct(
        protected string $cedula,
        protected string $connection,
    ) {}

    public function wherePivot(string $column, mixed $value): static
    {
        if ($column === 'role_id' || $column === 'rol_proyecto_id') {
            $this->rolProyectoId = (int) $value;

            return $this;
        }

        if ($column === 'rol_proyecto') {
            $this->rolProyectoId = match (strtolower((string) $value)) {
                'lider', 'líder' => self::ROL_LIDER,
                'autor' => self::ROL_AUTOR,
                default => is_numeric($value) ? (int) $value : null,
            };

            return $this;
        }

        return $this;
    }

    public function exists(): bool
    {
        $cacheKey = $this->cedula . ':' . ($this->rolProyectoId ?? 'any');

        $cached = User::recallEquiposExists($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            if (Schema::connection($this->connection)->hasTable('grupo_proyecto_estudiante')) {
                $query = DB::connection($this->connection)
                    ->table('grupo_proyecto_estudiante')
                    ->where(DB::raw('TRIM(gpe_ced_estudiante)'), $this->cedula);

                if ($this->rolProyectoId !== null) {
                    $query->where('gpe_rol_id', $this->rolProyectoId);
                }

                return User::rememberEquiposExists($cacheKey, $query->exists());
            }

            $service = app(IntranetEquipoSeccionService::class);
            if ($this->rolProyectoId === self::ROL_LIDER) {
                return User::rememberEquiposExists($cacheKey, $service->estudiantePuedeRegistrar($this->cedula));
            }

            return User::rememberEquiposExists($cacheKey, $service->equiposDelEstudiante($this->cedula)->isNotEmpty());
        } catch (\Throwable) {
            return User::rememberEquiposExists($cacheKey, false);
        }
    }
}
