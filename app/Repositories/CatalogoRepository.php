<?php

namespace App\Repositories;

use App\Models\Componente;
use App\Models\ComponentePrograma;
use App\Models\LapsoAcademico;
use App\Models\LineaInvestigacion;
use App\Models\MetodologiaInvestigacion;
use App\Models\ObjetivoInvestigacion;
use App\Models\TipoInvestigacion;
use App\Helpers\DualDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CatalogoRepository
{
    /**
     * @return array<string, Collection>
     */
    public function catalogos(?int $programaId = null, ?string $trayectoCodigo = null): array
    {
        return [
            'lineas' => $this->lineasPorPrograma($programaId),
            'metodologias' => $this->metodologiasActivas(),
            'tipos_investigacion' => $this->tiposInvestigacionActivos(),
            'objetivos_investigacion' => $this->objetivosInvestigacionActivos(),
            'lapsos' => Cache::remember('gestion_cat_lapsos', now()->addMinutes(10), fn() => $this->lapsosActivos()),
            'componentes_disp' => $this->componentesPorProgramaYTrayecto($programaId, $trayectoCodigo),
        ];
    }

    /**
     * Líneas activas filtradas por programa (coordinación).
     *
     * - Si programaId es null, devuelve todas (vista admin/sin contexto).
     * - Las líneas sin programa asignado (programa_id null/0) se consideran globales.
     */
    public function lineasPorPrograma(?int $programaId): Collection
    {
        $lineas = $this->lineasActivas();

        if ($programaId === null) {
            return $lineas;
        }

        return $lineas
            ->filter(fn ($l) => (int) ($l->programa_id ?? 0) === 0 || (int) ($l->programa_id ?? 0) === (int) $programaId)
            ->values();
    }

    public function invalidarCatalogos(): void
    {
        $keys = [
            'gestion_cat_lineas',
            'gestion_cat_metodologias',
            'gestion_cat_tipos_investigacion',
            'gestion_cat_objetivos_investigacion',
            'gestion_cat_lapsos',
            'gestion_comunidades_ordenadas',
        ];
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        // Limpiar caché de trayectos por programa (pro 1-20)
        for ($pro = 0; $pro <= 20; $pro++) {
            Cache::forget('cat_trayectos_programa_' . $pro);
        }
    }

    public function lineasActivas(): Collection
    {
        return LineaInvestigacion::where('activo', true)
            ->orderBy('nombre_investigacion')
            ->get();
    }

    public function metodologiasActivas(): Collection
    {
        return MetodologiaInvestigacion::where('estado_logico', true)->get();
    }

    public function tiposInvestigacionActivos(): Collection
    {
        return TipoInvestigacion::where('estado_logico', true)->get();
    }

    public function objetivosInvestigacionActivos(): Collection
    {
        return ObjetivoInvestigacion::where('estado_logico', true)->get();
    }

    public function lapsosActivos(): Collection
    {
        return LapsoAcademico::activos()->orderByDesc('lap_codigo')->get();
    }

    /**
     * Retorna componentes activos, filtrados por programa y trayecto.
     *
     * - Componentes SIN asignaciones en la tabla pivote se consideran GLOBALES (aparecen siempre).
     * - Componentes CON asignaciones solo aparecen si coinciden con (programaId, trayectoCodigo).
     * - Si trayectoCodigo es null, se muestran los que coinciden con cualquier trayecto del programa.
     * - Si programaId es null, retorna todos los activos (vista admin).
     */
    public function componentesPorProgramaYTrayecto(?int $programaId, ?string $trayectoCodigo = null): Collection
    {
        $cacheKey = 'gestion_componentes_v2_' . ($programaId ?? '0') . '_' . ($trayectoCodigo ?? '0');

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($programaId, $trayectoCodigo) {
            $query = Componente::where('estado_logico', true);

            if ($programaId !== null) {
                // Componentes que: NO tienen asignaciones (globales) O tienen asignación que coincide
                $query->where(function ($q) use ($programaId, $trayectoCodigo) {
                    $q->whereDoesntHave('programas')
                      ->orWhereHas('programas', function ($q) use ($programaId, $trayectoCodigo) {
                          $q->where('pro_codigo', $programaId);
                          if ($trayectoCodigo !== null && $trayectoCodigo !== '') {
                              $q->where(function ($q) use ($trayectoCodigo) {
                                  $q->where('tra_codigo', $trayectoCodigo)
                                    ->orWhereNull('tra_codigo');
                              });
                          }
                      });
                });
            }

            return $query->orderBy('nombre')->get();
        });
    }

    /**
     * Retorna todos los componentes activos (global, sin filtro).
     */
    public function componentesGlobales(): Collection
    {
        return Componente::where('estado_logico', true)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Retorna la lista de programas desde intranet para el selector en ComponenteManager.
     */
    public function programasDisponibles(): Collection
    {
        $conn = DualDatabase::academicConnection();
        return Cache::remember('cat_programas_todos', now()->addHours(24), function () use ($conn) {
            try {
                return DB::connection($conn)
                    ->table('programa')
                    ->select(['pro_codigo', 'pro_siglas', 'pro_nombre'])
                    ->orderBy('pro_siglas')
                    ->get();
            } catch (\Throwable) {
                return collect();
            }
        });
    }

    /**
     * Retorna los trayectos disponibles para un programa específico.
     * Usa la misma lógica que IntranetEquipoSeccionService::trayectosEnLapso():
     * seccion → semestre → trayecto, filtrado por malla → programa.
     * Excluye INICIAL y TRANSICIÓN. Si $proCodigo es 0, retorna todos.
     */
    public function trayectosPorPrograma(int $proCodigo): Collection
    {
        $conn = DualDatabase::academicConnection();
        $cacheKey = 'cat_trayectos_programa_v2_' . $proCodigo;

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($conn, $proCodigo) {
            try {
                if ($proCodigo > 0) {
                    // Misma ruta que usa IntranetEquipoSeccionService:
                    // seccion → semestre → trayecto, filtrado por malla → programa
                    $trayectos = DB::connection($conn)
                        ->table('seccion as sec')
                        ->join('semestre as sem', 'sem.sem_codigo', '=', 'sec.sec_cod_semestre')
                        ->join('trayecto as tra', 'tra.tra_codigo', '=', 'sem.sem_cod_trayecto')
                        ->join('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
                        ->where('mal.mal_cod_programa', $proCodigo)
                        ->whereNotIn('tra.tra_nombre', ['INICIAL', 'TRANSICIÓN'])
                        ->select('tra.tra_codigo', 'tra.tra_nombre')
                        ->distinct()
                        ->orderBy('tra.tra_codigo')
                        ->get();

                    if ($trayectos->isNotEmpty()) {
                        return $trayectos;
                    }
                }

                // Fallback: todos los trayectos (sin INICIAL y TRANSICIÓN)
                return DB::connection($conn)
                    ->table('trayecto')
                    ->whereNotIn('tra_nombre', ['INICIAL', 'TRANSICIÓN'])
                    ->orderBy('tra_codigo')
                    ->get(['tra_codigo', 'tra_nombre']);
            } catch (\Throwable) {
                return collect();
            }
        });
    }

    public function componenteProgramaExists(int $compCodigo, int $proCodigo): bool
    {
        return ComponentePrograma::where('comp_codigo', $compCodigo)
            ->where('pro_codigo', $proCodigo)
            ->exists();
    }

    public function componenteProgramaCreate(int $compCodigo, int $proCodigo): void
    {
        ComponentePrograma::create([
            'comp_codigo' => $compCodigo,
            'pro_codigo' => $proCodigo,
        ]);
    }

    public function componenteProgramaDeleteExcept(int $compCodigo, int $exceptProCodigo): void
    {
        ComponentePrograma::where('comp_codigo', $compCodigo)
            ->where('pro_codigo', '!=', $exceptProCodigo)
            ->delete();
    }

    /**
     * Obtiene el program_id de un componente a partir de su primera asignacion en componente_programa.
     */
    public function programaDeComponente(int $compCodigo): ?int
    {
        $asignacion = ComponentePrograma::where('comp_codigo', $compCodigo)->first();
        return $asignacion ? (int) $asignacion->pro_codigo : null;
    }

    /**
     * Sincroniza las asignaciones de un componente (programa, trayecto, cantidad).
     * Reemplaza todas las asignaciones existentes por las nuevas.
     *
     * @param  array<array{pro_codigo: int, tra_codigo: string|null, cantidad?: int|null}>  $asignaciones
     */
    public function sincronizarAsignaciones(int $compCodigo, array $asignaciones): void
    {
        ComponentePrograma::where('comp_codigo', $compCodigo)->delete();

        foreach ($asignaciones as $asig) {
            $proCodigo = (int) ($asig['pro_codigo'] ?? 0);
            if ($proCodigo <= 0) continue;
            $traCodigo = !empty($asig['tra_codigo']) ? (string) $asig['tra_codigo'] : null;
            $cantidad = isset($asig['cantidad']) ? (int) $asig['cantidad'] : 1;

            ComponentePrograma::create([
                'comp_codigo' => $compCodigo,
                'pro_codigo' => $proCodigo,
                'tra_codigo' => $traCodigo,
                'cantidad' => $cantidad,
            ]);
        }
    }

    /**
     * Sincroniza las asignaciones de TODOS los componentes para un programa específico (PNF).
     * Útil para la vista de vinculación del coordinador.
     *
     * @param  int  $proCodigo  PNF destino
     * @param  array<int, array{activo: bool, tra_codigo: string|null, cantidad: int}>  $componentesAsignaciones  key = comp_codigo
     */
    public function sincronizarAsignacionesPorPrograma(int $proCodigo, array $componentesAsignaciones): void
    {
        foreach ($componentesAsignaciones as $compCodigo => $data) {
            $compCodigo = (int) $compCodigo;
            if ($compCodigo <= 0) continue;

            $activo = (bool) ($data['activo'] ?? false);
            $traCodigo = !empty($data['tra_codigo']) ? (string) $data['tra_codigo'] : null;
            $cantidad = isset($data['cantidad']) ? max(1, (int) $data['cantidad']) : 1;

            if ($activo) {
                // Upsert: si ya existe la asignación (comp+pro+tra), actualiza cantidad; si no, crea
                ComponentePrograma::updateOrCreate(
                    [
                        'comp_codigo' => $compCodigo,
                        'pro_codigo' => $proCodigo,
                        'tra_codigo' => $traCodigo,
                    ],
                    ['cantidad' => $cantidad]
                );
            } else {
                // Eliminar la asignación si existe
                ComponentePrograma::where('comp_codigo', $compCodigo)
                    ->where('pro_codigo', $proCodigo)
                    ->where('tra_codigo', $traCodigo)
                    ->delete();
            }
        }
    }

    public function catalogoVacios(array $datos): array
    {
        $faltantes = [];

        if (($datos['comunidades'] ?? collect())->isEmpty()) {
            $faltantes[] = 'comunidades';
        }
        if (($datos['lineas'] ?? collect())->isEmpty()) {
            $faltantes[] = 'líneas de investigación';
        }
        if (($datos['metodologias'] ?? collect())->isEmpty()) {
            $faltantes[] = 'metodologías';
        }
        if (($datos['tipos_investigacion'] ?? collect())->isEmpty()) {
            $faltantes[] = 'tipos de investigación';
        }
        if (($datos['objetivos_investigacion'] ?? collect())->isEmpty()) {
            $faltantes[] = 'objetivos de investigación';
        }

        return $faltantes;
    }
}
