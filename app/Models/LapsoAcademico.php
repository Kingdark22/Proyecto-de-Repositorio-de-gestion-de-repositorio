<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Lapso académico: solo lectura desde intranet (no se crea ni edita en repositorio).
 */
class LapsoAcademico extends IntranetModel
{
    protected $table = 'lapso_academico';

    protected $primaryKey = 'lap_codigo';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'lap_fecha_inicio' => 'date',
        'lap_fecha_fin' => 'date',
    ];

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where(
            'lap_estatus',
            config('proyecto_profesor.lapso_estatus_activo', 'A')
        );
    }

    public static function vigente(): ?self
    {
        return static::activos()->orderByDesc('lap_codigo')->first();
    }

    public function getIdAttribute(): mixed
    {
        return $this->lap_codigo;
    }

    public function getNombreAttribute(): ?string
    {
        return $this->lap_nombre;
    }

    public function getFechaInicioAttribute()
    {
        return $this->lap_fecha_inicio;
    }

    public function getFechaFinAttribute()
    {
        return $this->lap_fecha_fin;
    }

    public function getEstadoLapsoAttribute(): bool
    {
        return $this->lap_estatus === config('proyecto_profesor.lapso_estatus_activo', 'A');
    }

    public function save(array $options = []): bool
    {
        throw new \RuntimeException('El lapso académico se administra en intranet; no puede guardarse desde el repositorio.');
    }

    public function delete(): ?bool
    {
        throw new \RuntimeException('El lapso académico se administra en intranet; no puede eliminarse desde el repositorio.');
    }
}
