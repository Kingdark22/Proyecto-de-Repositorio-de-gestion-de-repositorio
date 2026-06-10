<?php

namespace App\Models;

use App\Helpers\DbHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Inscripcion extends Model
{
    protected $table = 'inscripcion';
    protected $connection = 'intranet';

    public $timestamps = false;

    protected $fillable = [
        'ins_codigo',
        'ins_cedula',
        'ins_cod_seccion_unidad_docente',
        'ins_estatus',
    ];

    public function getConnectionName()
    {
        return $this->connection ?: DbHelper::connection();
    }

    public function scopeConEstatusActivo(Builder $query): Builder
    {
        $estatusActivos = config('equipo_proyecto.inscripcion_estatus_activo', ['A']);
        return $query->whereIn('ins_estatus', $estatusActivos);
    }

    public function scopePorCedula(Builder $query, string $cedula): Builder
    {
        return $query->whereRaw('TRIM(ins_cedula) = ?', [trim($cedula)]);
    }

    public function scopeEnLapso(Builder $query, ?int $lapCodigo): Builder
    {
        if ($lapCodigo === null) {
            return $query;
        }
        return $query->where('lap.lap_codigo', $lapCodigo);
    }

    public function scopeEnSeccion(Builder $query, int $secCodigo): Builder
    {
        return $query->where('sec.sec_codigo', $secCodigo);
    }

    public static function baseQuery(): Builder
    {
        return static::query()
            ->join('seccion_unidad_docente as sud', 'sud.sud_codigo', '=', 'ins_cod_seccion_unidad_docente')
            ->join('seccion as sec', 'sec.sec_codigo', '=', 'sud.sud_cod_seccion')
            ->join('lapso_academico as lap', 'lap.lap_codigo', '=', 'sec.sec_cod_lapso_academico')
            ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
            ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
            ->conEstatusActivo()
            ->where('lap.lap_estatus', config('proyecto_profesor.lapso_estatus_activo', 'A'));
    }

    public static function equiposDelEstudiante(string $cedula, ?int $lapCodigo = null): Collection
    {
        $cedula = trim($cedula);
        if ($cedula === '') {
            return collect();
        }

        try {
            $query = static::baseQuery()
                ->porCedula($cedula)
                ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
                ->select([
                    'sec.sec_codigo',
                    'sec.sec_nombre',
                    'lap.lap_codigo',
                    'lap.lap_nombre',
                    'pro.pro_siglas',
                    'pro.pro_nombre',
                    'tra.tra_nombre',
                ])
                ->enLapso($lapCodigo)
                ->distinct()
                ->orderByDesc('lap.lap_codigo')
                ->orderBy('sec.sec_nombre');

            return $query->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    public static function estudiantePerteneceSeccion(string $cedula, int $lapCodigo, int $secCodigo): bool
    {
        $cedula = trim($cedula);
        if ($cedula === '') {
            return false;
        }

        try {
            return static::baseQuery()
                ->porCedula($cedula)
                ->enLapso($lapCodigo)
                ->enSeccion($secCodigo)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public static function programasEnLapso(?int $lapCodigo): Collection
    {
        if ($lapCodigo === null) {
            return collect();
        }

        try {
            return static::baseQuery()
                ->enLapso($lapCodigo)
                ->select(['pro.pro_codigo', 'pro.pro_siglas', 'pro.pro_nombre'])
                ->whereNotNull('pro.pro_codigo')
                ->distinct()
                ->orderBy('pro.pro_siglas')
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    public static function seccionesEnLapso(?int $lapCodigo, ?int $programaCodigo = null): Collection
    {
        if ($lapCodigo === null) {
            return collect();
        }

        try {
            $query = static::baseQuery()
                ->enLapso($lapCodigo)
                ->select(['sec.sec_codigo', 'sec.sec_nombre', 'pro.pro_siglas'])
                ->distinct();

            if ($programaCodigo) {
                $query->where('pro.pro_codigo', $programaCodigo);
            }

            return $query->orderBy('sec.sec_nombre')->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    public static function listarEquiposPorSeccion(array $filtros = []): Collection
    {
        if (empty($filtros['lapso'])) {
            return collect();
        }

        try {
            $query = static::baseQuery()
                ->select([
                    'sec.sec_codigo',
                    'sec.sec_nombre',
                    'lap.lap_codigo',
                    'lap.lap_nombre',
                    'pro.pro_siglas',
                    'pro.pro_nombre',
                ])
                ->selectRaw('COUNT(DISTINCT TRIM(ins.ins_cedula)) as integrantes')
                ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
                ->addSelect('tra.tra_nombre');

            if (! empty($filtros['lapso'])) {
                $query->enLapso((int) $filtros['lapso']);
            }
            if (! empty($filtros['programa'])) {
                $query->where('pro.pro_codigo', (int) $filtros['programa']);
            }
            if (! empty($filtros['seccion'])) {
                $query->enSeccion((int) $filtros['seccion']);
            }
            if (! empty($filtros['busqueda'])) {
                $term = '%'.mb_strtolower(trim($filtros['busqueda'])).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(TRIM(sec.sec_nombre)) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(TRIM(pro.pro_siglas)) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(TRIM(lap.lap_nombre)) LIKE ?', [$term]);
                });
            }

            return $query
                ->groupBy(
                    'sec.sec_codigo',
                    'sec.sec_nombre',
                    'lap.lap_codigo',
                    'lap.lap_nombre',
                    'pro.pro_siglas',
                    'pro.pro_nombre',
                    'tra.tra_nombre'
                )
                ->orderByDesc('lap.lap_codigo')
                ->orderBy('sec.sec_nombre')
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }
}
