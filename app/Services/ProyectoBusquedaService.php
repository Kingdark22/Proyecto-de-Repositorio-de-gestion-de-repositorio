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
        return Proyecto::with([
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
            'linea_investigacion',
            'metodologia',
            'tipo_investigacion',
            'objetivo_investigacion',
            'comunidad',
            'documentos',
            'vinculaciones.tituloVinculacion',
            'vinculaciones.comunidad',
        ])
            ->visiblesPublico();

        $this->aplicarFiltroEquipo($query, $equipoFiltro);

        // ─── Búsqueda por texto libre en TODA la información del proyecto ───
        $termino = trim((string) ($filtros['search'] ?? ''));
        if ($termino !== '') {
            // Unir grupo_proyecto_modulo para buscar también por nombre del grupo
            $query->leftJoin('grupo_proyecto_modulo as gpm', function ($join) {
                $join->on('proyectos.pry_direccion_logica', '=', 'gpm.grp_identificador')
                     ->orWhereRaw('proyectos.pry_direccion_logica = \'EQGRP:\' || CAST(gpm.grp_codigo AS TEXT)');
            });

            $query->select('proyectos.*')
                  ->where(function (Builder $q) use ($termino) {
                // ── Campos directos del proyecto ──
                $q->whereRaw('proyectos.pry_resumen ILIKE ?', ['%' . $termino . '%'])
                  ->orWhere('proyectos.pry_direccion_logica', 'ILIKE', '%' . $termino . '%');

                // ── Nombre del grupo de proyecto (título) ──
                $q->orWhere('gpm.grp_nombre', 'ILIKE', '%' . $termino . '%');

                // ── Miembros del grupo (nombre, apellido, cédula) ──
                $q->orWhereRaw('gpm.grp_miembros::text ILIKE ?', ['%' . $termino . '%']);

                // ── Comunidad original ──
                $q->orWhereHas('comunidad', function (Builder $cq) use ($termino) {
                    $cq->where('com_nombre', 'ILIKE', '%' . $termino . '%');
                });

                // ── Línea de investigación ──
                $q->orWhereHas('linea_investigacion', function (Builder $lq) use ($termino) {
                    $lq->where('lin_nombre_investigacion', 'ILIKE', '%' . $termino . '%');
                });

                // ── Metodología ──
                $q->orWhereHas('metodologia', function (Builder $mq) use ($termino) {
                    $mq->where('mei_nombre', 'ILIKE', '%' . $termino . '%');
                });

                // ── Tipo de investigación ──
                $q->orWhereHas('tipo_investigacion', function (Builder $tq) use ($termino) {
                    $tq->where('tin_nombre', 'ILIKE', '%' . $termino . '%');
                });

                // ── Objetivo de investigación ──
                $q->orWhereHas('objetivo_investigacion', function (Builder $oq) use ($termino) {
                    $oq->where('obi_nombre', 'ILIKE', '%' . $termino . '%');
                });

                // ── Vinculaciones – título ──
                $q->orWhereHas('vinculaciones.tituloVinculacion', function (Builder $vq) use ($termino) {
                    $vq->where('tiv_titulo', 'ILIKE', '%' . $termino . '%');
                });

                // ── Vinculaciones – comunidad vinculada ──
                $q->orWhereHas('vinculaciones.comunidad', function (Builder $vcq) use ($termino) {
                    $vcq->where('com_nombre', 'ILIKE', '%' . $termino . '%');
                });

                // ── Documentos – nombre del componente ──
                $q->orWhereHas('documentos.componente', function (Builder $dcq) use ($termino) {
                    $dcq->where('comp_nombre', 'ILIKE', '%' . $termino . '%');
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

        // Evitar duplicados cuando hay LEFT JOIN con grupo_proyecto_modulo
        if ($termino !== '') {
            $query->distinct();
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

        if ($seccion && $lap) {
            return [
                'tipo' => 'exacto',
                'valor' => $this->equipoSeccion->construirClave((int) $lap, (int) $seccion),
            ];
        }

        if ($programa || $trayecto) {
            if (! $lap) {
                return 'todos';
            }

            $secciones = $this->intranet->seccionesEnLapso(
                (int) $lap,
                $programa ? (int) $programa : null,
                $trayecto ? (int) $trayecto : null
            );

            if ($secciones->isEmpty()) {
                return 'sin_resultados';
            }

            $claves = $secciones
                ->map(fn ($sec) => $this->equipoSeccion->construirClave((int) $lap, (int) $sec->sec_codigo))
                ->unique()
                ->values()
                ->all();

            // Incluir también grupos registrados para este lapso/programa
            try {
                $grupos = app(\App\Services\GrupoProyectoService::class)->listar([
                    'lapso' => (int) $lap,
                    'programa' => $programa ? (int) $programa : null,
                    'seccion' => $seccion ? (int) $seccion : null,
                ]);
                $clavesGrupos = $grupos
                    ->pluck('clave')

                    ->values()
                    ->all();
                $claves = array_unique(array_merge($claves, $clavesGrupos));
            } catch (\Throwable) {
                // Si falla la consulta de grupos, continuar solo con las secciones
            }

            return ['tipo' => 'lista', 'valor' => $claves];
        }

        if ($lap) {
            // Construir lista combinada: EQSEC para cada sección + EQGRP para cada grupo en este lapso
            $secciones = $this->intranet->seccionesEnLapso((int) $lap);
            $claves = $secciones
                ->map(fn ($sec) => $this->equipoSeccion->construirClave((int) $lap, (int) $sec->sec_codigo))
                ->unique()
                ->values()
                ->all();

            try {
                $grupos = app(\App\Services\GrupoProyectoService::class)->listar([
                    'lapso' => (int) $lap,
                ]);
                $clavesGrupos = $grupos
                    ->pluck('clave')

                    ->values()
                    ->all();
                $claves = array_unique(array_merge($claves, $clavesGrupos));
            } catch (\Throwable) {
                // Si falla la consulta de grupos, continuar solo con las secciones
            }

            return ['tipo' => 'lista', 'valor' => $claves];
        }

        return 'todos';
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
