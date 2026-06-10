<?php

namespace App\Services;

use App\Helpers\DbHelper;
use App\Helpers\DualDatabase;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Relaciona datos entre intranet (solo lectura) y repositorio (escritura del módulo)
 * sin claves foráneas entre bases: se unen en PHP por cédula, lapso, sección, claves EQGRP, etc.
 */
class ConexionDualService
{
    /**
     * Conexión activa para LEER datos académicos (intranet o simulación si intranet cae).
     */
    public function conexionLecturaAcademica(): string
    {
        return DbHelper::connection();
    }

    /**
     * Si en esta petición las lecturas vienen de intranet (no solo simulación).
     */
    public function leyendoDesdeIntranet(): bool
    {
        return DbHelper::isUsingIntranet();
    }

    /**
     * Conexión MySQL del módulo — único destino de INSERT/UPDATE del repositorio SOGAC.
     */
    public function conexionEscrituraModulo(): string
    {
        return DualDatabase::repositorioConnection();
    }

    /**
     * Consulta SOLO LECTURA sobre tablas académicas (intranet/simulación). No usar para guardar.
     */
    public function consultaAcademica(string $tabla): Builder
    {
        if (! DualDatabase::isIntranetTable($tabla)) {
            throw new RuntimeException("{$tabla} no es tabla académica; use consultaModulo().");
        }

        return DualDatabase::tablaAcademicaSoloLectura($tabla);
    }

    /**
     * Consulta de tablas propias del módulo (MySQL repositorio).
     */
    public function consultaModulo(string $tabla): Builder
    {
        return DualDatabase::repositorioTable($tabla);
    }

    /**
     * Tras leer filas de intranet, opcionalmente espejar a simulación (nunca a repositorio ni a intranet).
     *
     * @param  iterable<int, object|array<string, mixed>>  $filas
     */
    public function espejarLecturaIntranetASimulacion(string $tabla, iterable $filas): int
    {
        return app(IntranetSimulationMirrorService::class)->mirrorRows($tabla, $filas);
    }

    /**
     * Relación lógica: grupo registrado (repositorio) + contexto de sección (intranet).
     *
     * @return array{grupo: object, seccion: object|null}
     */
    public function relacionarGrupoConSeccionAcademica(string $claveEqgrp): array
    {
        $grupo = app(GrupoProyectoService::class)->obtenerPorClave($claveEqgrp);
        if (! $grupo) {
            return ['grupo' => null, 'seccion' => null];
        }

        try {
            $seccion = $this->consultaAcademica('seccion')
                ->join('lapso_academico as lap', 'lap.lap_codigo', '=', 'sec.sec_cod_lapso_academico')
                ->where('sec.sec_codigo', $grupo->sec_codigo)
                ->where('lap.lap_codigo', $grupo->lap_codigo)
                ->select(['sec.*', 'lap.lap_nombre'])
                ->first();
        } catch (\Throwable) {
            $seccion = null;
        }

        return ['grupo' => $grupo, 'seccion' => $seccion];
    }

    /**
     * Prohibido escribir en intranet desde el módulo.
     */
    public function asegurarNoEscrituraIntranet(string $tabla): void
    {
        if (DualDatabase::isIntranetTable($tabla)) {
            throw new RuntimeException(
                "No se puede escribir en [{$tabla}] de intranet desde el módulo. Use consultaModulo() en repositorio."
            );
        }
    }
}
