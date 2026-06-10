<?php

namespace App\Models;

use App\Helpers\DbHelper;
use App\Helpers\DualDatabase;
use App\Services\UserRoleService;
use App\Support\UserEquiposQuery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'usu_cedula';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = ['*'];

    /** @var array<string, bool> */
    protected static array $equiposExistsCache = [];

    protected ?object $personaRowCache = null;

    protected bool $personaLoaded = false;

    public function getConnectionName()
    {
        return $this->connection ?: \App\Helpers\DbHelper::connection();
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        // No-op
    }

    public function getRememberTokenName()
    {
        return '';
    }

    public function getAuthPassword()
    {
        return $this->usu_clave;
    }

    protected function casts(): array
    {
        return [];
    }

    protected function personaRow(): ?object
    {
        if ($this->personaLoaded) {
            return $this->personaRowCache;
        }

        $this->personaLoaded = true;

        try {
            $this->personaRowCache = DualDatabase::table('persona')
                ->whereRaw('TRIM(per_cedula) = ?', [trim((string) $this->usu_cedula)])
                ->first();
        } catch (\Throwable) {
            $this->personaRowCache = null;
        }

        return $this->personaRowCache;
    }

    public function getNombreAttribute()
    {
        $persona = $this->personaRow();

        return $persona && ! empty(trim($persona->per_nombres ?? ''))
            ? trim($persona->per_nombres)
            : trim($this->usu_nombre);
    }

    public function getApellidoAttribute()
    {
        $persona = $this->personaRow();

        return $persona ? trim($persona->per_apellidos ?? '') : '';
    }

    public function getModalidadAttribute()
    {
        $roleService = app(UserRoleService::class);
        $activeLabel = $roleService->activeRoleLabel($this);

        if ($activeLabel) {
            return 'Rol activo: ' . $activeLabel;
        }

        return Cache::remember('user_modalidad_' . $this->usu_cedula, 300, function () {
            $cedulaTrimmed = trim((string) $this->usu_cedula);

            try {
                $nombre = trim($this->usu_nombre);
                if ($nombre === 'PROGRAMADOR' || $nombre === 'admin' || $this->usu_cod_rol == 1) {
                    return 'Administrador';
                }

                $student = DualDatabase::table('estudiante')
                    ->whereRaw('TRIM(est_cedula) = ?', [$cedulaTrimmed])
                    ->first();

                if ($student) {
                    $program = DualDatabase::table('programa')
                        ->where('pro_codigo', $student->est_cod_programa)
                        ->first();

                    $programName = $program ? trim($program->pro_siglas ?? $program->pro_nombre) : 'Estudiante';

                    return 'Estudiante (' . $programName . ')';
                }

                $isTeacher = DualDatabase::table('seccion_unidad_docente')
                    ->whereRaw('TRIM(sud_ced_docente) = ?', [$cedulaTrimmed])
                    ->exists();

                if ($isTeacher) {
                    return 'Docente';
                }
            } catch (\Throwable) {
                return 'Usuario';
            }

            return 'Usuario Externo';
        });
    }

    public function hasRole(...$roles)
    {
        return app(UserRoleService::class)->userHasRole($this, ...$roles);
    }

    /**
     * @return array<string, string>
     */
    public function availableRoles(): array
    {
        return app(UserRoleService::class)->detectAvailableRoles($this);
    }

    public static function recallEquiposExists(string $key): ?bool
    {
        return array_key_exists($key, static::$equiposExistsCache)
            ? static::$equiposExistsCache[$key]
            : null;
    }

    public static function rememberEquiposExists(string $key, bool $value): bool
    {
        return static::$equiposExistsCache[$key] = $value;
    }

    public function equipos(): UserEquiposQuery
    {
        return new UserEquiposQuery(
            trim((string) $this->usu_cedula),
            DbHelper::connection(),
        );
    }

    public function perteneceAEquipo(?string $rolProyecto = null): bool
    {
        $query = $this->equipos();

        if ($rolProyecto !== null) {
            $query->wherePivot('rol_proyecto', $rolProyecto);
        }

        return $query->exists();
    }

    public function puedeRegistrarProyecto(): bool
    {
        return app(\App\Services\ProyectoGestionService::class)->usuarioPuedeRegistrar($this);
    }

    /**
     * Configuración local del módulo (trayecto/sección) si el docente está habilitado en intranet.
     */
    public function profesorProyectoModulo(): ?object
    {
        $service = app(\App\Services\IntranetProfessorService::class);
        $lapso = $service->lapsosActivos()->first();
        if (! $lapso) {
            return null;
        }

        $cfg = $service->configuracionModulo(trim((string) $this->usu_cedula), (int) $lapso->lap_codigo);
        if (! $cfg || ! ($cfg['ppm_habilitado'] ?? false)) {
            return null;
        }

        return (object) $cfg;
    }
}
