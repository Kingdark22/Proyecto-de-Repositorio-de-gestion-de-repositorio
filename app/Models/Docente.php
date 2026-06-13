<?php

namespace App\Models;

use App\Helpers\DbHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Docente extends Model
{
    protected $table = 'seccion_unidad_docente';
    protected $connection = 'intranet';

    public $timestamps = false;

    protected $fillable = [
        'sud_codigo',
        'sud_cod_seccion',
        'sud_cod_unidad',
        'sud_ced_docente',
        'sud_estatus',
    ];

    public function getConnectionName()
    {
        return $this->connection ?: DbHelper::connection();
    }

    public function scopeConCedulaValida(Builder $query): Builder
    {
        return $query->whereRaw("TRIM(sud_ced_docente) NOT LIKE '%-%'")
            ->whereRaw('LENGTH(TRIM(sud_ced_docente)) >= 6');
    }

    public function scopeActivos(Builder $query): Builder
    {
        $estatusActivo = config('proyecto_profesor.sud_estatus_activo');
        if ($estatusActivo) {
            return $query->where('sud_estatus', $estatusActivo);
        }
        return $query;
    }

    public function scopePorCedula(Builder $query, string $cedula): Builder
    {
        return $query->whereRaw('TRIM(sud_ced_docente) = ?', [trim($cedula)]);
    }

    public static function esDocente(string $cedula): bool
    {
        try {
            return static::conCedulaValida()
                ->porCedula($cedula)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public static function asignacionesEnLapso(?int $lapCodigo = null, array $filtros = []): Builder
    {
        $query = static::query()
            ->join('seccion as sec', 'sec.sec_codigo', '=', 'sud_cod_seccion')
            ->join('lapso_academico as lap', 'lap.lap_codigo', '=', 'sec.sec_cod_lapso_academico')
            ->join('unidad_curricular as ucu', 'ucu.ucu_codigo', '=', 'sud_cod_unidad')
            ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
            ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
            ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
            ->where('lap.lap_estatus', config('proyecto_profesor.lapso_estatus_activo', 'A'))
            ->conCedulaValida()
            ->activos();

        if ($lapCodigo !== null) {
            $query->where('lap.lap_codigo', $lapCodigo);
        }

        if (! empty($filtros['programa'])) {
            $query->where('pro.pro_codigo', (int) $filtros['programa']);
        }

        if (! empty($filtros['trayecto'])) {
            $query->where('tra.tra_codigo', (int) $filtros['trayecto']);
        }

        if (! empty($filtros['seccion'])) {
            $query->where('sec.sec_codigo', (int) $filtros['seccion']);
        }

        return $query;
    }

    public static function asignacionesProyecto(?int $lapCodigo = null, array $filtros = []): Builder
    {
        $query = static::asignacionesEnLapso($lapCodigo, $filtros);
        return static::aplicarFiltroUnidadProyecto($query);
    }

    protected static function aplicarFiltroUnidadProyecto(Builder $query): Builder
    {
        $prefijos = config('proyecto_profesor.unidad_siglas_prefijos', []);
        $patrones = config('proyecto_profesor.unidad_nombre_patrones', []);

        if ($prefijos === [] && $patrones === []) {
            return $query;
        }

        return $query->where(function ($q) use ($prefijos, $patrones) {
            foreach ($prefijos as $prefijo) {
                $prefijo = trim((string) $prefijo);
                if ($prefijo !== '') {
                    $q->orWhereRaw('TRIM(ucu.ucu_siglas) ILIKE ?', [$prefijo.'%']);
                }
            }
            foreach ($patrones as $patron) {
                $patron = trim((string) $patron);
                if ($patron !== '') {
                    $q->orWhereRaw('UPPER(TRIM(ucu.ucu_nombre)) LIKE ?', ['%'.$patron.'%']);
                }
            }
        });
    }
}
