<?php

namespace App\Services;

use App\Helpers\DualDatabase;
use App\Models\RepositorioModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Acceso unificado a tablas del módulo (MySQL repositorio).
 * Relación con intranet solo en PHP vía ConexionDualService / servicios académicos.
 */
class ModuloRepositorioService
{
    public function __construct(
        protected ConexionDualService $conexionDual,
    ) {}

    public function conexion(): string
    {
        return $this->conexionDual->conexionEscrituraModulo();
    }

    public function tablaExiste(string $tabla): bool
    {
        return Cache::remember('tabla_existe_'.$tabla, 3600, function () use ($tabla) {
            return DualDatabase::hasTable($this->conexion(), $tabla);
        });
    }

    public function esTablaModulo(string $tabla): bool
    {
        return DualDatabase::isRepositorioTable($tabla);
    }

    public function consulta(string $tabla): Builder
    {
        return $this->conexionDual->consultaModulo($tabla);
    }

    /**
     * @param  class-string<RepositorioModel>  $modelClass
     */
    public function queryModel(string $modelClass): Builder
    {
        /** @var RepositorioModel $model */
        $model = new $modelClass;
        $tabla = $model->getTable();

        if (! $this->esTablaModulo($tabla) && ! $this->tablaExiste($tabla)) {
            throw new \RuntimeException("La tabla [{$tabla}] no está disponible en repositorio.");
        }

        return $modelClass::query();
    }

    /**
     * @param  class-string<RepositorioModel>  $modelClass
     * @return Collection<int, RepositorioModel>
     */
    public function listarActivos(string $modelClass, string $columnaActivo = 'activo'): Collection
    {
        if (! $this->tablaExiste((new $modelClass)->getTable())) {
            return collect();
        }

        $query = $this->queryModel($modelClass);

        if ($columnaActivo === 'estado_logico') {
            return $query->where('estado_logico', true)->orderBy('nombre')->get();
        }

        return $query->where($columnaActivo, true)->orderBy('nombre')->get();
    }

    /**
     * Une un registro de repositorio con datos académicos de intranet (solo lectura).
     *
     * @param  array<string, mixed>  $contextoAcademico  p. ej. lap_codigo, sec_codigo
     * @return array{modulo: object|null, academico: object|null}
     */
    public function relacionarConAcademico(
        string $tablaModulo,
        int|string $idModulo,
        string $columnaPk,
        string $tablaAcademica,
        array $contextoAcademico,
        array $columnasAcademicas = ['*'],
    ): array {
        if (! $this->tablaExiste($tablaModulo)) {
            return ['modulo' => null, 'academico' => null];
        }

        $modulo = $this->consulta($tablaModulo)->where($columnaPk, $idModulo)->first();

        if ($modulo === null || ! DualDatabase::isIntranetTable($tablaAcademica)) {
            return ['modulo' => $modulo, 'academico' => null];
        }

        try {
            $query = $this->conexionDual->consultaAcademica($tablaAcademica);
            foreach ($contextoAcademico as $col => $val) {
                if ($val !== null && $val !== '') {
                    $query->where($col, $val);
                }
            }
            $academico = $query->select($columnasAcademicas)->first();
        } catch (\Throwable) {
            $academico = null;
        }

        return ['modulo' => $modulo, 'academico' => $academico];
    }

    /**
     * @param  class-string<Model&RepositorioModel>  $modelClass
     * @param  array<string, mixed>  $legacyAttributes  atributos lógicos (nombre, activo, …)
     */
    public function guardarCatalogo(string $modelClass, array $legacyAttributes, int|string|null $id = null): Model
    {
        if ($id) {
            $item = $this->queryModel($modelClass)->where((new $modelClass)->getKeyName(), $id)->firstOrFail();
            $item->fill($legacyAttributes);
            $item->save();

            return $item;
        }

        return $modelClass::create($legacyAttributes);
    }

    public function lineasInvestigacionActivas(): Collection
    {
        return Cache::remember('modulo_lineas_investigacion_activas', now()->addMinutes(10), function () {
            if (! $this->tablaExiste('linea_investigacions')) {
                return collect();
            }

            return $this->queryModel(\App\Models\LineaInvestigacion::class)
                ->where('activo', true)
                ->orderBy('nombre_investigacion')
                ->get();
        });
    }
}
