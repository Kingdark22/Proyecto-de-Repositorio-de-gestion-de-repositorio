<?php

namespace App\Repositories;

use App\Models\GrupoProyectoModulo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrupoProyectoRepository
{
    public function conexionRepositorio(): string
    {
        return (string) config('dual_database.repositorio_connection', 'pgsql');
    }

    public function tablaDisponible(): bool
    {
        return Cache::remember('grp_tabla_disponible', 3600, function () {
            try {
                return Schema::connection($this->conexionRepositorio())->hasTable('grupo_proyecto_modulo');
            } catch (\Throwable) {
                return false;
            }
        });
    }

    public function columnaId(): string
    {
        return Cache::remember('grp_columna_id', 3600, function () {
            $conn = $this->conexionRepositorio();

            return Schema::connection($conn)->hasColumn('grupo_proyecto_modulo', 'grp_codigo')
                ? 'grp_codigo'
                : 'gpb_codigo';
        });
    }

    public function find(int $id): ?GrupoProyectoModulo
    {
        return GrupoProyectoModulo::find($id);
    }

    /**
     * @return Collection<int, GrupoProyectoModulo>
     */
    public function all(): Collection
    {
        return GrupoProyectoModulo::query()
            ->select(['grp_codigo', 'grp_nombre', 'grp_contexto', 'grp_com_codigo', 'grp_creador_cedula', 'grp_miembros'])
            ->orderByDesc($this->columnaId())
            ->get();
    }

    /**
     * @param  array{lapso?: int|null, programa?: int|null, seccion?: int|array|null, trayecto?: string|null, equipo?: string|null, busqueda?: string|null, creador?: string|null}  $filtros
     * @return Collection<int, GrupoProyectoModulo>
     */
    public function listar(array $filtros = []): Collection
    {
        $version = Cache::get('grp_cache_version') ?? 1;
        $cacheKey = 'grp_listar_' . $version . '_' . md5(json_encode($filtros));

        return Cache::remember($cacheKey, now()->addMinutes(2), function () use ($filtros) {
            $query = GrupoProyectoModulo::query()
                ->select(['grp_codigo', 'grp_nombre', 'grp_contexto', 'grp_com_codigo', 'grp_creador_cedula', 'grp_miembros']);

            if (!empty($filtros['lapso'])) {
                $query->whereRaw('CAST(grp_contexto AS jsonb)->>\'lap_codigo\' = ?', [(string) $filtros['lapso']]);
            }
            if (!empty($filtros['programa'])) {
                $query->whereRaw('CAST(grp_contexto AS jsonb)->>\'pro_codigo\' = ?', [(string) $filtros['programa']]);
            }
            if (!empty($filtros['seccion'])) {
                if (is_array($filtros['seccion'])) {
                    $query->where(function ($q) use ($filtros) {
                        foreach ($filtros['seccion'] as $sec) {
                            $q->orWhereRaw('CAST(grp_contexto AS jsonb)->>\'sec_codigo\' = ?', [(string) $sec]);
                        }
                    });
                } else {
                    $query->whereRaw('CAST(grp_contexto AS jsonb)->>\'sec_codigo\' = ?', [(string) $filtros['seccion']]);
                }
            }
            if (!empty($filtros['trayecto'])) {
                $query->whereRaw('CAST(grp_contexto AS jsonb)->>\'tra_codigo\' = ?', [(string) $filtros['trayecto']]);
            }
            if (!empty($filtros['equipo'])) {
                $query->whereJsonContains('grp_miembros', ['cedula' => $filtros['equipo']]);
            }
            if (!empty($filtros['busqueda'])) {
                $term = '%' . mb_strtolower(trim((string) $filtros['busqueda'])) . '%';
                $query->whereRaw('LOWER(grp_nombre) LIKE ?', [$term]);
            }
            if (!empty($filtros['creador'])) {
                $query->where('grp_creador_cedula', trim((string) $filtros['creador']));
            }

            return $query->orderByDesc($this->columnaId())->get();
        });
    }

    /**
     * @return Collection<int, GrupoProyectoModulo>
     */
    public function findByMiembroCedula(string $cedula): Collection
    {
        try {
            return GrupoProyectoModulo::whereRaw(
                "CAST(grp_miembros AS jsonb) @> ?",
                ['[{"cedula":"' . $cedula . '"}]']
            )->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    /**
     * @return Collection<int, GrupoProyectoModulo>
     */
    public function findLiderByCedula(string $cedula): Collection
    {
        try {
            return GrupoProyectoModulo::whereRaw(
                "CAST(grp_miembros AS jsonb) @> ?",
                ['[{"cedula":"' . $cedula . '","rol_id":1}]']
            )->get(['grp_codigo']);
        } catch (\Throwable) {
            return collect();
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload): int
    {
        return (int) (new GrupoProyectoModulo)->insertGetId($payload, $this->columnaId());
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(int $id, array $payload): void
    {
        GrupoProyectoModulo::where($this->columnaId(), $id)->update($payload);
    }

    public function delete(int $id): void
    {
        DB::connection($this->conexionRepositorio())
            ->table('grupo_proyecto_modulo')
            ->where($this->columnaId(), $id)
            ->delete();
    }

    /**
     * @param  array<string, mixed>  $miembros
     */
    public function updateMiembros(int $id, array $miembros): void
    {
        $model = GrupoProyectoModulo::find($id);
        if ($model) {
            $model->update(['grp_miembros' => $miembros]);
        }
    }

    public function invalidarCache(): void
    {
        Cache::put('grp_cache_version', time(), now()->addDays(1));
    }

    /**
     * @return Collection<int, GrupoProyectoModulo>
     */
    public function findByContextoSeccion(int $lapCodigo, int $secCodigo): Collection
    {
        return GrupoProyectoModulo::whereRaw(
            "CAST(grp_contexto AS jsonb)->>'lap_codigo' = ? AND CAST(grp_contexto AS jsonb)->>'sec_codigo' = ?",
            [(string) $lapCodigo, (string) $secCodigo]
        )->get();
    }

    /**
     * Verifica si un nombre de grupo está disponible. Si se proporciona $lapCodigo,
     * verifica dentro de ese lapso; si es null, verifica globalmente.
     * Usa LOWER() con índice funcional para búsqueda case-insensitive eficiente.
     */
    public function nombreDisponibleEnLapso(string $nombre, ?int $lapCodigo = null, ?int $excludeGrpCodigo = null): bool
    {
        $query = GrupoProyectoModulo::whereRaw(
            'LOWER(grp_nombre) = ?',
            [mb_strtolower(trim($nombre))]
        );

        if ($lapCodigo !== null) {
            $query->whereRaw(
                "CAST(grp_contexto AS jsonb)->>'lap_codigo' = ?",
                [(string) $lapCodigo]
            );
        }

        if ($excludeGrpCodigo !== null) {
            $query->where('grp_codigo', '!=', $excludeGrpCodigo);
        }

        return ! $query->exists();
    }

    /**
     * Verifica si una cédula pertenece a algún grupo en el lapso indicado.
     * Usa el índice GIN sobre grp_miembros para la búsqueda.
     */
    public function estudianteEnGrupoEnLapso(string $cedula, int $lapCodigo, ?int $excludeGrpCodigo = null): bool
    {
        $query = GrupoProyectoModulo::whereRaw(
            'CAST(grp_miembros AS jsonb) @> ? AND CAST(grp_contexto AS jsonb)->>\'lap_codigo\' = ?',
            [json_encode([['cedula' => trim($cedula)]]), (string) $lapCodigo]
        );

        if ($excludeGrpCodigo !== null) {
            $query->where('grp_codigo', '!=', $excludeGrpCodigo);
        }

        return $query->exists();
    }
}
