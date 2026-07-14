<?php

namespace App\Services;

use App\Models\Comunidad;
use App\Models\LapsoAcademico;
use App\Models\LineaInvestigacion;
use App\Models\MetodologiaInvestigacion;
use App\Models\Proyecto;
use App\Models\TipoInvestigacion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorInstance;
use Illuminate\Support\Facades\Cache;

class ProyectoBusquedaService
{
    public function __construct(
        protected IntranetProfessorService $intranet,
        protected IntranetEquipoSeccionService $equipoSeccion,
    ) {}

    /**
     * @param  array{
     *     search?: string,
     *     lapso?: int|null,
     *     programa?: int|null,
     *     trayecto?: int|null,
     *     seccion?: int|null,
     *     comunidad?: int|null,
     *     linea?: int|null,
     *     tipo_investigacion?: int|null,
     *     metodologia?: int|null,
     * }  $filtros
     * @return array<string, mixed>
     */
    public function datosVista(array $filtros, int $page): array
    {
        $lapCodigo = $filtros['lapso'] ?? null;
        $programaCodigo = $filtros['programa'] ?? null;
        $trayectoCodigo = $filtros['trayecto'] ?? null;

        $lapsos = Cache::remember('busqueda_lapsos_activos', now()->addMinutes(10), fn() =>
            LapsoAcademico::activos()->orderByDesc('lap_codigo')->get()
        );
        $intranetDisponible = $this->intranet->lapsosActivos()->isNotEmpty();

        $catTtl = now()->addMinutes(10);

        return [
            'proyectos' => $this->buscar($filtros, $page),
            'lapsos' => $lapsos,
            'intranetDisponible' => $intranetDisponible,
            'programas' => $lapCodigo && $intranetDisponible
                ? $this->intranet->programasEnLapso($lapCodigo)
                : collect(),
            'trayectosCatalogo' => $lapCodigo && $intranetDisponible
                ? $this->intranet->trayectosEnLapso($lapCodigo, $programaCodigo)
                : collect(),
            'secciones' => $lapCodigo && $intranetDisponible
                ? $this->intranet->seccionesEnLapso($lapCodigo, $programaCodigo, $trayectoCodigo)
                : collect(),
            'seccionesDesdeGrupos' => $lapCodigo ? $this->seccionesDesdeGrupos($lapCodigo, $programaCodigo, $trayectoCodigo) : collect(),
            'comunidades' => Cache::remember('busqueda_comunidades', $catTtl, fn() => Comunidad::orderBy('nombre')->get()),
            'lineas' => app(ModuloRepositorioService::class)->lineasInvestigacionActivas(),
            'tipos_investigacion' => Cache::remember('busqueda_tipos_investigacion', $catTtl, fn() => TipoInvestigacion::where('estado_logico', true)->orderBy('nombre')->get()),
            'metodologias' => Cache::remember('busqueda_metodologias', $catTtl, fn() => MetodologiaInvestigacion::where('estado_logico', true)->orderBy('nombre')->get()),
        ];
    }

    public function proyectoDetalle(int $id): ?Proyecto
    {
        $proyecto = Proyecto::with([
            'linea_investigacion',
            'metodologia',
            'tipo_investigacion',
            'objetivo_investigacion',
            'comunidad',
            'documentos.componente',
            'vinculaciones.tituloVinculacion',
            'vinculaciones.comunidad',
        ])
            ->visiblesPublico()
            ->find($id);

        if ($proyecto && $proyecto->equipo_ref) {
            $grupo = \App\Models\GrupoProyectoModulo::porIdentificador($proyecto->equipo_ref);
            $proyecto->setRelation('grupoDetalle', $grupo);
        }

        return $proyecto;
    }

    /**
     * @param  array{
     *     search?: string,
     *     lapso?: int|null,
     *     programa?: int|null,
     *     trayecto?: int|null,
     *     seccion?: int|null,
     *     comunidad?: int|null,
     *     linea?: int|null,
     *     tipo_investigacion?: int|null,
     *     metodologia?: int|null,
     * }  $filtros
     */
    public function buscar(array $filtros, int $page): LengthAwarePaginator
    {
        $equipoFiltro = $this->resolverFiltroEquipo($filtros);

        if ($equipoFiltro === 'sin_resultados') {
            return new PaginatorInstance([], 0, 10, $page, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        }

        $query = Proyecto::with([
            'comunidad',
            'documentos.componente',
            'vinculaciones.tituloVinculacion',
            'vinculaciones.comunidad',
        ])
            ->visiblesPublico();

        $this->aplicarFiltroEquipo($query, $equipoFiltro);

        // ─── Búsqueda por texto libre en TODA la información del proyecto ───
        $termino = trim((string) ($filtros['search'] ?? ''));
        if ($termino !== '') {
            $termino = '%' . $termino . '%';
            $query->where(function (Builder $q) use ($termino) {
                // ── Resumen ──
                $q->whereRaw('proyectos.pry_resumen ILIKE ?', [$termino]);

                // ── Referencia equipo ──
                $q->orWhereRaw('proyectos.pry_direccion_logica ILIKE ?', [$termino]);

                // ── Nombre del grupo ──
                $q->orWhereRaw('EXISTS (SELECT 1 FROM grupo_proyecto_modulo gpm WHERE (gpm.grp_identificador = proyectos.pry_direccion_logica OR gpm.grp_codigo::text = regexp_replace(proyectos.pry_direccion_logica, E\'^EQGRP:\', \'\')) AND gpm.grp_nombre ILIKE ?)', [$termino]);

                // ── Miembros del grupo (grp_miembros: nombre, apellido, cedula) ──
                $q->orWhereRaw('EXISTS (SELECT 1 FROM grupo_proyecto_modulo gpm WHERE (gpm.grp_identificador = proyectos.pry_direccion_logica OR gpm.grp_codigo::text = regexp_replace(proyectos.pry_direccion_logica, E\'^EQGRP:\', \'\')) AND gpm.grp_miembros::text ILIKE ?)', [$termino]);

                // ── Profesor (creador del grupo por cédula y nombre en contexto) ──
                $q->orWhereRaw('EXISTS (SELECT 1 FROM grupo_proyecto_modulo gpm WHERE (gpm.grp_identificador = proyectos.pry_direccion_logica OR gpm.grp_codigo::text = regexp_replace(proyectos.pry_direccion_logica, E\'^EQGRP:\', \'\')) AND (gpm.grp_creador_cedula::text ILIKE ? OR gpm.grp_contexto::text ILIKE ?))', [$termino, $termino]);

                // ── Comunidad ──
                $q->orWhereHas('comunidad', function (Builder $cq) use ($termino) {
                    $cq->whereRaw('com_nombre ILIKE ?', [$termino]);
                });

                // ── Línea de investigación ──
                $q->orWhereHas('linea_investigacion', function (Builder $lq) use ($termino) {
                    $lq->whereRaw('lin_nombre_investigacion ILIKE ?', [$termino]);
                });

                // ── Metodología ──
                $q->orWhereHas('metodologia', function (Builder $mq) use ($termino) {
                    $mq->whereRaw('mei_nombre ILIKE ?', [$termino]);
                });

                // ── Tipo de investigación ──
                $q->orWhereHas('tipo_investigacion', function (Builder $tq) use ($termino) {
                    $tq->whereRaw('tin_nombre ILIKE ?', [$termino]);
                });

                // ── Objetivo de investigación ──
                $q->orWhereHas('objetivo_investigacion', function (Builder $oq) use ($termino) {
                    $oq->whereRaw('obi_nombre ILIKE ?', [$termino]);
                });

                // ── Vinculaciones – título ──
                $q->orWhereHas('vinculaciones.tituloVinculacion', function (Builder $vq) use ($termino) {
                    $vq->whereRaw('tiv_titulo ILIKE ?', [$termino]);
                });

                // ── Vinculaciones – comunidad vinculada ──
                $q->orWhereHas('vinculaciones.comunidad', function (Builder $vcq) use ($termino) {
                    $vcq->whereRaw('com_nombre ILIKE ?', [$termino]);
                });

                // ── Documentos – nombre del componente ──
                $q->orWhereHas('documentos.componente', function (Builder $dcq) use ($termino) {
                    $dcq->whereRaw('comp_nombre ILIKE ?', [$termino]);
                });
            });
        }

        if (! empty($filtros['comunidad'])) {
            $query->where('comunidad_id', (int) $filtros['comunidad']);
        }
        if (! empty($filtros['linea'])) {
            $query->where('linea_investigacion_id', (int) $filtros['linea']);
        }
        if (! empty($filtros['tipo_investigacion'])) {
            $query->where('tipo_investigacion_id', (int) $filtros['tipo_investigacion']);
        }
        if (! empty($filtros['metodologia'])) {
            $query->where('metodologia_id', (int) $filtros['metodologia']);
        }

        return $query->latest('proyectos.created_at')->paginate(10, page: $page);
    }

    /**
     * @param  array{
     *     lapso?: int|null,
     *     programa?: int|null,
     *     trayecto?: int|null,
     *     seccion?: int|null,
     * }  $filtros
     * @return 'todos'|'sin_resultados'|array{tipo: string, valor: string|array<int, string>}
     */
    protected function resolverFiltroEquipo(array $filtros): string|array
    {
        $lap = $filtros['lapso'] ?? null;
        $seccion = $filtros['seccion'] ?? null;
        $programa = $filtros['programa'] ?? null;
        $trayecto = $filtros['trayecto'] ?? null;

        if (! $lap && ! $seccion && ! $programa && ! $trayecto) {
            return 'todos';
        }

        try {
            $grupos = app(\App\Services\GrupoProyectoService::class)->listar(array_filter([
                'lapso' => $lap ? (int) $lap : null,
                'programa' => $programa ? (int) $programa : null,
                'seccion' => $seccion ? (int) $seccion : null,
                'trayecto' => $trayecto ? (int) $trayecto : null,
            ], fn ($v) => $v !== null));

            $claves = $grupos->pluck('clave')->values()->all();
            return empty($claves) ? 'sin_resultados' : ['tipo' => 'lista', 'valor' => $claves];
        } catch (\Throwable) {
            return 'todos';
        }
    }

    /**
     * @param  'todos'|'sin_resultados'|array{tipo: string, valor: string|array<int, string>}  $equipoFiltro
     */
    protected function aplicarFiltroEquipo(Builder $query, string|array $equipoFiltro): void
    {
        if ($equipoFiltro === 'todos') {
            return;
        }

        if ($equipoFiltro === 'sin_resultados') {
            $query->whereRaw('1 = 0');

            return;
        }

        match ($equipoFiltro['tipo']) {
            'exacto' => $query->where('equipo_ref', $equipoFiltro['valor']),
            'lista' => $query->whereIn('equipo_ref', $equipoFiltro['valor']),
            'prefijo' => $query->where('equipo_ref', 'ILIKE', $equipoFiltro['valor'] . '%'),
            default => null,
        };
    }

    protected function seccionesDesdeGrupos(?int $lapCodigo, ?int $programaCodigo = null, ?int $trayectoCodigo = null): \Illuminate\Support\Collection
    {
        if ($lapCodigo === null) {
            return collect();
        }

        try {
            $grupos = app(\App\Services\GrupoProyectoService::class)->listar([
                'lapso' => $lapCodigo,
                'programa' => $programaCodigo,
            ]);

            $secVistos = [];
            $secciones = collect();

            foreach ($grupos as $g) {
                if ($g->sec_codigo && !in_array($g->sec_codigo, $secVistos, true)) {
                    if ($trayectoCodigo === null || (int) ($g->tra_codigo ?? 0) === $trayectoCodigo) {
                        $secVistos[] = $g->sec_codigo;
                        $secciones->push((object) [
                            'sec_codigo' => $g->sec_codigo,
                            'sec_nombre' => $g->sec_nombre,
                            'pro_siglas' => $g->pro_siglas,
                        ]);
                    }
                }
            }

            return $secciones->sortBy('sec_nombre')->values();
        } catch (\Throwable) {
            return collect();
        }
    }
}
