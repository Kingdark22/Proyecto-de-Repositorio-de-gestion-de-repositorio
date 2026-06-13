<?php

namespace App\Services;

use App\Models\Comunidad;
use App\Models\LapsoAcademico;
use App\Models\LineaInvestigacion;
use App\Models\MetodologiaInvestigacion;
use App\Models\Proyecto;
use App\Models\TipoInvestigacion;
use App\Models\TipoPublicacion;
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
     *     tipo_publicacion?: int|null,
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
            'comunidades' => Cache::remember('busqueda_comunidades', $catTtl, fn() => Comunidad::orderBy('nombre')->get()),
            'lineas' => app(ModuloRepositorioService::class)->lineasInvestigacionActivas(),
            'tipos_publicacion' => Cache::remember('busqueda_tipos_publicacion', $catTtl, fn() => TipoPublicacion::where('estado_logico', true)->orderBy('nombre')->get()),
            'tipos_investigacion' => Cache::remember('busqueda_tipos_investigacion', $catTtl, fn() => TipoInvestigacion::where('estado_logico', true)->orderBy('nombre')->get()),
            'metodologias' => Cache::remember('busqueda_metodologias', $catTtl, fn() => MetodologiaInvestigacion::where('estado_logico', true)->orderBy('nombre')->get()),
        ];
    }

    public function proyectoDetalle(int $id): ?Proyecto
    {
        return Proyecto::with([
            'tipo_publicacion',
            'linea_investigacion',
            'metodologia',
            'tipo_investigacion',
            'comunidad',
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
     *     tipo_publicacion?: int|null,
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

        $query = Proyecto::with(['tipo_publicacion', 'linea_investigacion', 'comunidad'])
            ->visiblesPublico();

        $this->aplicarFiltroEquipo($query, $equipoFiltro);

        $termino = trim((string) ($filtros['search'] ?? ''));
        if ($termino !== '') {
            $query->where(function (Builder $q) use ($termino) {
                $q->whereRaw('pry_resumen ILIKE ?', ['%'.$termino.'%']);
            });
        }

        if (! empty($filtros['comunidad'])) {
            $query->where('comunidad_id', (int) $filtros['comunidad']);
        }
        if (! empty($filtros['linea'])) {
            $query->where('linea_investigacion_id', (int) $filtros['linea']);
        }
        if (! empty($filtros['tipo_publicacion'])) {
            $query->where('tipo_publicacion_id', (int) $filtros['tipo_publicacion']);
        }
        if (! empty($filtros['tipo_investigacion'])) {
            $query->where('tipo_investigacion_id', (int) $filtros['tipo_investigacion']);
        }
        if (! empty($filtros['metodologia'])) {
            $query->where('metodologia_id', (int) $filtros['metodologia']);
        }

        return $query->latest()->paginate(10, page: $page);
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

            return ['tipo' => 'lista', 'valor' => $claves];
        }

        if ($lap) {
            return [
                'tipo' => 'prefijo',
                'valor' => IntranetEquipoSeccionService::PREFIJO_REF.':'.(int) $lap.':',
            ];
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
            'lista' => $query->whereIn('pry_direccion_logica', $equipoFiltro['valor']),
            'prefijo' => $query->where('equipo_ref', 'like', $equipoFiltro['valor'].'%'),
            default => null,
        };
    }
}
