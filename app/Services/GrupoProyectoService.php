<?php

namespace App\Services;

use App\Repositories\GrupoProyectoRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Models\GrupoProyectoModulo;

/**
 * Grupo de proyecto en repositorio (solo MySQL módulo).
 * Contexto académico (lapso/sección/PNF) en grp_contexto JSON; lectura intranet solo en PHP vía otros servicios.
 * Clave EQGRP:{id}. Sin FK ni columnas hacia intranet.
 */
class GrupoProyectoService
{
    public const PREFIJO = 'EQGRP';

    public function __construct(
        protected IntranetEquipoSeccionService $equipos,
        protected GrupoProyectoRepository $repo,
    ) {}

    public function tablaDisponible(): bool
    {
        return $this->repo->tablaDisponible();
    }

    public function conexionRepositorio(): string
    {
        return $this->repo->conexionRepositorio();
    }

    public function usaGruposIntranet(): bool
    {
        return $this->equipos->tablaGrupoProyectoExiste();
    }

    public function construirClave(int $grpCodigo): string
    {
        return self::PREFIJO.':'.$grpCodigo;
    }

    /**
     * @return array{tipo: string, grp_codigo?: int, lap_codigo?: int, sec_codigo?: int}|null
     */
    public function parsearClave(?string $clave): ?array
    {
        if ($clave === null || $clave === '') {
            return null;
        }

        if (preg_match('/^'.self::PREFIJO.':(\d+)$/', $clave, $m)) {
            return ['tipo' => self::PREFIJO, 'grp_codigo' => (int) $m[1]];
        }

        if (preg_match('/^'.IntranetEquipoSeccionService::PREFIJO_REF.':(\d+):(\d+)$/', $clave, $m)) {
            return [
                'tipo' => IntranetEquipoSeccionService::PREFIJO_REF,
                'lap_codigo' => (int) $m[1],
                'sec_codigo' => (int) $m[2],
            ];
        }

        return null;
    }

    /**
     * @param  list<array{cedula: string, rol_id: int, nombre?: string, apellido?: string}>  $miembros
     */
    /**
     * @param  array{lap_nombre?: string, sec_nombre?: string, pro_siglas?: string, pro_nombre?: string}|null  $etiquetasAcademicas
     */
    public function registrar(
        string $nombre,
        int $lapCodigo,
        int $secCodigo,
        ?int $proCodigo,
        ?int $comCodigo,
        array $miembros,
        string $creadorCedula,
        ?int $grpCodigo = null,
        ?array $etiquetasAcademicas = null,
    ): ?string {
        if (! $this->tablaDisponible()) {
            return null;
        }

        $miembros = $this->normalizarMiembros($miembros);
        if ($miembros === []) {
            return null;
        }

        $contexto = array_filter(array_merge([
            'lap_codigo' => $lapCodigo,
            'sec_codigo' => $secCodigo,
            'pro_codigo' => $proCodigo,
            'lap_nombre' => trim((string) ($etiquetasAcademicas['lap_nombre'] ?? '')),
            'sec_nombre' => trim((string) ($etiquetasAcademicas['sec_nombre'] ?? '')),
            'pro_siglas' => trim((string) ($etiquetasAcademicas['pro_siglas'] ?? '')),
            'pro_nombre' => trim((string) ($etiquetasAcademicas['pro_nombre'] ?? '')),
        ], $etiquetasAcademicas ?? []), fn ($v) => $v !== null && $v !== '' && $v !== 0);

        $payload = [
            'grp_nombre' => trim($nombre),
            'grp_contexto' => json_encode($contexto, JSON_UNESCAPED_UNICODE),
            'grp_com_codigo' => $comCodigo,
            'grp_creador_cedula' => trim($creadorCedula),
            'grp_miembros' => json_encode($miembros, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ];

        $this->repo->invalidarCache();

        if ($grpCodigo) {
            $this->repo->update($grpCodigo, $payload);

            return $this->construirClave($grpCodigo);
        }

        $payload['created_at'] = now();
        $id = $this->repo->create($payload);

        return $this->construirClave($id);
    }

    public function obtenerPorClave(string $clave): ?object
    {
        $partes = $this->parsearClave($clave);
        if (($partes['tipo'] ?? '') !== self::PREFIJO || empty($partes['grp_codigo'])) {
            return null;
        }

        return $this->obtener((int) $partes['grp_codigo']);
    }

    public function obtener(int $grpCodigo): ?object
    {
        if (! $this->tablaDisponible()) {
            return null;
        }

        $idCol = $this->repo->columnaId();
        $row = GrupoProyectoModulo::where($idCol, $grpCodigo)->first();

        return $row ? $this->enriquecerEtiquetasAcademicas($this->mapearFila($row)) : null;
    }

    /**
     * @param  array{lapso?: int|null, programa?: int|null, seccion?: int|null, creador?: string|null, busqueda?: string}  $filtros
     * @return Collection<int, object>
     */
    public function listar(array $filtros = []): Collection
    {
        if (! $this->tablaDisponible()) {
            return collect();
        }

        $cacheEtiquetas = [];

        return $this->repo->listar($filtros)
            ->map(fn ($r) => $this->enriquecerEtiquetasAcademicas($this->mapearFila($r), $cacheEtiquetas))
            ->values();
    }

    /**
     * Grupos registrados en repositorio (para listados del módulo).
     *
     * @param  array{lapso?: int|null, programa?: int|null, seccion?: int|null, busqueda?: string}  $filtros
     * @return Collection<int, object>
     */
    public function listarEquipos(array $filtros = []): Collection
    {
        return $this->listar($filtros);
    }

    public function eliminar(int $grpCodigo): void
    {
        if (! $this->tablaDisponible()) {
            return;
        }

        $this->repo->invalidarCache();
        $this->repo->delete($grpCodigo);
    }

    /**
     * @return Collection<int, object>
     */
    public function equiposDelEstudiante(string $cedula, ?int $lapCodigo = null): Collection
    {
        $cedula = trim($cedula);

        return $this->listar(['lapso' => $lapCodigo])
            ->filter(fn ($g) => collect($g->miembros)->contains(
                fn ($m) => trim((string) ($m['cedula'] ?? '')) === $cedula
            ))
            ->map(fn ($g) => $this->mapearEquipoRegistrado($g, $cedula))
            ->values();
    }

    public function claveEquipo(int $lapCodigo, int $secCodigo): string
    {
        return $this->equipos->construirClave($lapCodigo, $secCodigo);
    }

    /**
     * @return Collection<int, object{cedula: string, nombre: string, apellido: string, rol: string}>
     */
    public function integrantes(string $claveEquipo): Collection
    {
        if (str_starts_with($claveEquipo, self::PREFIJO.':')) {
            return $this->integrantesDesdeClave($claveEquipo);
        }

        return $this->equipos->integrantes($claveEquipo);
    }

    public function resumen(string $claveEquipo): string
    {
        if (str_starts_with($claveEquipo, self::PREFIJO.':')) {
            $g = $this->obtenerPorClave($claveEquipo);

            return $g ? 'Grupo '.$g->nombre.' ('.$g->integrantes.' integrantes)' : '—';
        }

        return $this->equipos->resumenEquipo($claveEquipo);
    }

    public function estudianteEnGrupo(string $cedula, string $clave, ?int $rolRequerido = null): bool
    {
        if (! str_starts_with($clave, self::PREFIJO.':')) {
            return $this->equipos->estudiantePerteneceEquipo($cedula, $clave, $rolRequerido);
        }

        $grupo = $this->obtenerPorClave($clave);
        if (! $grupo) {
            return false;
        }

        $cedula = trim($cedula);
        foreach ($grupo->miembros as $m) {
            if (trim((string) ($m['cedula'] ?? '')) !== $cedula) {
                continue;
            }

            return $rolRequerido === null || (int) ($m['rol_id'] ?? 0) === $rolRequerido;
        }

        return false;
    }

    /**
     * @return Collection<int, object>
     */
    public function candidatosSeccion(int $lapCodigo, int $secCodigo): Collection
    {
        return $this->equipos->integrantes(
            $this->equipos->construirClave($lapCodigo, $secCodigo)
        );
    }

    /**
     * @return Collection<int, object{cedula: string, nombre: string, apellido: string, rol: string, rol_id: int}>
     */
    public function integrantesDesdeClave(string $clave): Collection
    {
        $grupo = $this->obtenerPorClave($clave);
        if (! $grupo) {
            return collect();
        }

        return collect($grupo->miembros)->map(fn ($m) => (object) [
            'cedula' => $m['cedula'],
            'nombre' => $m['nombre'] ?? '',
            'apellido' => $m['apellido'] ?? '',
            'rol_id' => (int) ($m['rol_id'] ?? 0),
            'rol' => $this->etiquetaRol((int) ($m['rol_id'] ?? 0)),
        ]);
    }

    /**
     * @return object|null
     */
    public function encapsular(string $claveEquipo): ?object
    {
        if (str_starts_with($claveEquipo, self::PREFIJO.':')) {
            $g = $this->obtenerPorClave($claveEquipo);
            if (! $g) {
                return null;
            }

            return (object) [
                'id' => $g->clave,
                'clave' => $g->clave,
                'nombre' => $g->nombre,
                'resumen' => $this->resumen($claveEquipo),
                'lap_codigo' => $g->lap_codigo,
                'sec_codigo' => $g->sec_codigo,
                'integrantes' => $g->integrantes,
                'miembros' => $this->integrantesDesdeClave($claveEquipo),
                'origen' => 'grupo_proyecto_modulo',
            ];
        }

        $partes = $this->equipos->parsearClave($claveEquipo);
        if ($partes === null) {
            return null;
        }

        $miembros = $this->integrantes($claveEquipo);

        return (object) [
            'id' => $claveEquipo,
            'clave' => $claveEquipo,
            'nombre' => $this->resumen($claveEquipo),
            'resumen' => $this->resumen($claveEquipo),
            'lap_codigo' => $partes['lap_codigo'],
            'sec_codigo' => $partes['sec_codigo'],
            'integrantes' => $miembros->count(),
            'miembros' => $miembros,
            'origen' => $this->usaGruposIntranet() ? 'grupo_proyecto_intranet' : 'seccion_inscripcion',
        ];
    }

    public function columnaId(): string
    {
        return $this->repo->columnaId();
    }

    /**
     * @return array{
     *     lap_codigo: int,
     *     sec_codigo: int,
     *     pro_codigo: int|null,
     *     lap_nombre: string,
     *     sec_nombre: string,
     *     pro_siglas: string,
     *     pro_nombre: string
     * }
     */
    protected function decodificarContexto(object $row): array
    {
        $vacio = [
            'lap_codigo' => 0,
            'sec_codigo' => 0,
            'pro_codigo' => null,
            'lap_nombre' => '',
            'sec_nombre' => '',
            'pro_siglas' => '',
            'pro_nombre' => '',
        ];

        $raw = $row->grp_contexto ?? null;

        if (($raw instanceof \ArrayObject || $raw instanceof \Illuminate\Database\Eloquent\Casts\AsArrayObject) || (is_object($raw) && method_exists($raw, 'getArrayCopy'))) {
            $ctx = (array) $raw;
            if (count($ctx) > 0) {
                return [
                    'lap_codigo' => (int) ($ctx['lap_codigo'] ?? 0),
                    'sec_codigo' => (int) ($ctx['sec_codigo'] ?? 0),
                    'pro_codigo' => isset($ctx['pro_codigo']) ? (int) $ctx['pro_codigo'] : null,
                    'lap_nombre' => trim((string) ($ctx['lap_nombre'] ?? '')),
                    'sec_nombre' => trim((string) ($ctx['sec_nombre'] ?? '')),
                    'pro_siglas' => trim((string) ($ctx['pro_siglas'] ?? '')),
                    'pro_nombre' => trim((string) ($ctx['pro_nombre'] ?? '')),
                ];
            }
        }

        if (is_string($raw) && $raw !== '') {
            $ctx = json_decode($raw, true);
            if (is_array($ctx)) {
                return [
                    'lap_codigo' => (int) ($ctx['lap_codigo'] ?? 0),
                    'sec_codigo' => (int) ($ctx['sec_codigo'] ?? 0),
                    'pro_codigo' => isset($ctx['pro_codigo']) ? (int) $ctx['pro_codigo'] : null,
                    'lap_nombre' => trim((string) ($ctx['lap_nombre'] ?? '')),
                    'sec_nombre' => trim((string) ($ctx['sec_nombre'] ?? '')),
                    'pro_siglas' => trim((string) ($ctx['pro_siglas'] ?? '')),
                    'pro_nombre' => trim((string) ($ctx['pro_nombre'] ?? '')),
                ];
            }
        }

        return $vacio;
    }

    /**
     * @param  array<string, array{lap_nombre: string, sec_nombre: string, pro_siglas: string, pro_nombre: string}>  $cache
     */
    protected function enriquecerEtiquetasAcademicas(object $grupo, array &$cache = []): object
    {
        if ($grupo->sec_nombre !== '' && $grupo->pro_siglas !== '' && $grupo->lap_nombre !== '') {
            $grupo->resumen_pnf_sec = $this->formatearResumenPnfSec($grupo);

            return $grupo;
        }

        if ($grupo->lap_codigo <= 0 || $grupo->sec_codigo <= 0) {
            $grupo->resumen_pnf_sec = '—';

            return $grupo;
        }

        $cacheKey = $grupo->lap_codigo.':'.$grupo->sec_codigo.':'.($grupo->pro_codigo ?? '');
        if (! isset($cache[$cacheKey])) {
            $cache[$cacheKey] = $this->equipos->etiquetasContexto(
                (int) $grupo->lap_codigo,
                (int) $grupo->sec_codigo,
                $grupo->pro_codigo
            );
        }

        $etiq = $cache[$cacheKey];
        if ($grupo->lap_nombre === '') {
            $grupo->lap_nombre = $etiq['lap_nombre'];
        }
        if ($grupo->sec_nombre === '') {
            $grupo->sec_nombre = $etiq['sec_nombre'];
        }
        if ($grupo->pro_siglas === '') {
            $grupo->pro_siglas = $etiq['pro_siglas'];
        }
        if ($grupo->pro_nombre === '') {
            $grupo->pro_nombre = $etiq['pro_nombre'];
        }

        $grupo->resumen_pnf_sec = $this->formatearResumenPnfSec($grupo);

        return $grupo;
    }

    protected function formatearResumenPnfSec(object $grupo): string
    {
        $pnf = trim((string) ($grupo->pro_siglas ?? ''));
        if ($pnf === '') {
            $pnf = trim((string) ($grupo->pro_nombre ?? ''));
        }
        $sec = trim((string) ($grupo->sec_nombre ?? ''));

        if ($pnf !== '' && $sec !== '') {
            return $pnf.' · '.$sec;
        }
        if ($sec !== '') {
            return $sec;
        }
        if ($pnf !== '') {
            return $pnf;
        }

        return 'Sec. '.(int) $grupo->sec_codigo;
    }

    /**
     * @param  list<array{cedula: string, rol_id: int, nombre?: string, apellido?: string}>  $miembros
     * @return list<array{cedula: string, rol_id: int, nombre: string, apellido: string}>
     */
    protected function normalizarMiembros(array $miembros): array
    {
        $out = [];
        $tieneLider = false;

        foreach ($miembros as $m) {
            $cedula = trim((string) ($m['cedula'] ?? ''));
            if ($cedula === '') {
                continue;
            }
            $rolId = (int) ($m['rol_id'] ?? IntranetEquipoSeccionService::ROL_AUTOR);
            if ($rolId === IntranetEquipoSeccionService::ROL_LIDER) {
                $tieneLider = true;
            }
            $out[$cedula] = [
                'cedula' => $cedula,
                'rol_id' => $rolId,
                'nombre' => trim((string) ($m['nombre'] ?? '')),
                'apellido' => trim((string) ($m['apellido'] ?? '')),
            ];
        }

        if ($out === [] || ! $tieneLider) {
            return [];
        }

        return array_values($out);
    }

    protected function etiquetaRol(int $rolId): string
    {
        return match ($rolId) {
            IntranetEquipoSeccionService::ROL_LIDER => 'Líder',
            IntranetEquipoSeccionService::ROL_AUTOR => 'Autor',
            default => 'Integrante',
        };
    }

    protected function mapearFila(object $row): object
    {
        $rawMiembros = $row->grp_miembros ?? $row->gpb_miembros ?? '[]';

        if (is_array($rawMiembros) || is_object($rawMiembros)) {
            $miembros = (array) $rawMiembros;
        } else {
            $miembros = json_decode((string) $rawMiembros, true);
        }
        if (! is_array($miembros)) {
            $miembros = [];
        }

        $ctx = $this->decodificarContexto($row);
        $codigo = (int) ($row->grp_codigo ?? $row->gpb_codigo ?? 0);
        $clave = $this->construirClave($codigo);

        return (object) [
            'grp_codigo' => $codigo,
            'clave' => $clave,
            'id' => $clave,
            'nombre' => trim((string) ($row->grp_nombre ?? $row->gpb_nombre ?? '')),
            'lap_codigo' => $ctx['lap_codigo'],
            'sec_codigo' => $ctx['sec_codigo'],
            'pro_codigo' => $ctx['pro_codigo'],
            'lap_nombre' => $ctx['lap_nombre'],
            'sec_nombre' => $ctx['sec_nombre'],
            'pro_siglas' => $ctx['pro_siglas'],
            'pro_nombre' => $ctx['pro_nombre'],
            'resumen_pnf_sec' => '',
            'com_codigo' => ($row->grp_com_codigo ?? $row->gpb_com_codigo ?? null) ? (int) ($row->grp_com_codigo ?? $row->gpb_com_codigo) : null,
            'creador_cedula' => trim((string) ($row->grp_creador_cedula ?? $row->gpb_creador_cedula ?? '')),
            'miembros' => $miembros,
            'integrantes' => count($miembros),
            'origen' => 'grupo_proyecto_modulo',
            'es_grupo_registrado' => true,
        ];
    }

    protected function mapearEquipoRegistrado(object $grupo, ?string $cedulaEstudiante = null): object
    {
        $grupo = $this->enriquecerEtiquetasAcademicas($grupo);

        return (object) [
            'id' => $grupo->clave,
            'clave' => $grupo->clave,
            'nombre' => $grupo->nombre,
            'sec_codigo' => $grupo->sec_codigo,
            'sec_nombre' => $grupo->sec_nombre,
            'lap_codigo' => $grupo->lap_codigo,
            'lapso_nombre' => $grupo->lap_nombre,
            'programa_siglas' => $grupo->pro_siglas,
            'trayecto_nombre' => '',
            'integrantes' => $grupo->integrantes,
            'validado' => $cedulaEstudiante
                ? $this->estudianteEnGrupo($cedulaEstudiante, $grupo->clave)
                : true,
            'es_grupo_registrado' => true,
        ];
    }

    /**
     * Actualiza los roles de los miembros de un grupo EQGRP.
     * @param  list<string>  $leaders  Cédulas que serán líderes
     */
    public function asignarLideres(string $clave, array $leaders): void
    {
        if (!str_starts_with($clave, self::PREFIJO.':')) {
            return;
        }

        $grupo = $this->obtenerPorClave($clave);
        if (!$grupo) {
            return;
        }

        $miembros = [];
        foreach ($grupo->miembros as $m) {
            $cedula = trim((string) ($m['cedula'] ?? ''));
            if ($cedula === '') {
                continue;
            }
            $esLider = in_array($cedula, $leaders, true);
            $miembros[] = [
                'cedula' => $cedula,
                'rol_id' => $esLider ? IntranetEquipoSeccionService::ROL_LIDER : IntranetEquipoSeccionService::ROL_AUTOR,
                'nombre' => trim((string) ($m['nombre'] ?? '')),
                'apellido' => trim((string) ($m['apellido'] ?? '')),
            ];
        }

        $model = GrupoProyectoModulo::find($grupo->grp_codigo);
        if ($model) {
            $this->repo->updateMiembros($model->id, $miembros);
        }
    }
}
