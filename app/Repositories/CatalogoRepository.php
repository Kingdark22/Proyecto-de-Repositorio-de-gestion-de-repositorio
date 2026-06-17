<?php

namespace App\Repositories;

use App\Models\Componente;
use App\Models\ComponentePrograma;
use App\Models\LapsoAcademico;
use App\Models\LineaInvestigacion;
use App\Models\MetodologiaInvestigacion;
use App\Models\ObjetivoInvestigacion;
use App\Models\TipoInvestigacion;
use App\Models\TipoPublicacion;
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
        $ttl = now()->addMinutes(10);

        return [
            'lineas' => Cache::remember('gestion_cat_lineas', $ttl, fn() => $this->lineasActivas()),
            'metodologias' => Cache::remember('gestion_cat_metodologias', $ttl, fn() => $this->metodologiasActivas()),
            'tipos_publicacion' => Cache::remember('gestion_cat_tipos_publicacion', $ttl, fn() => $this->tiposPublicacionActivos()),
            'tipos_investigacion' => Cache::remember('gestion_cat_tipos_investigacion', $ttl, fn() => $this->tiposInvestigacionActivos()),
            'objetivos_investigacion' => Cache::remember('gestion_cat_objetivos_investigacion', $ttl, fn() => $this->objetivosInvestigacionActivos()),
            'lapsos' => Cache::remember('gestion_cat_lapsos', $ttl, fn() => $this->lapsosActivos()),
            'componentes_disp' => $this->componentesPorProgramaYTrayecto($programaId, $trayectoCodigo),
        ];
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

    public function tiposPublicacionActivos(): Collection
    {
        return TipoPublicacion::where('estado_logico', true)->get();
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
        return Cache::remember('cat_programas_todos', now()->addHours(2), function () use ($conn) {
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
     * Retorna los trayectos de un programa desde intranet, a través de malla.
     */
    public function trayectosPorPrograma(int $proCodigo): Collection
    {
        $conn = DualDatabase::academicConnection();
        $cacheKey = 'cat_trayectos_prog_' . $proCodigo;
        return Cache::remember($cacheKey, now()->addHours(2), function () use ($conn, $proCodigo) {
            try {
                return DB::connection($conn)
                    ->table('trayecto as tra')
                    ->join('malla as mal', 'mal.mal_cod_trayecto', '=', 'tra.tra_codigo')
                    ->where('mal.mal_cod_programa', $proCodigo)
                    ->select(['tra.tra_codigo', 'tra.tra_nombre'])
                    ->distinct()
                    ->orderBy('tra.tra_nombre')
                    ->get();
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
     * Sincroniza las asignaciones de un componente (programa, trayecto).
     * Reemplaza todas las asignaciones existentes por las nuevas.
     *
     * @param  array<array{pro_codigo: int, tra_codigo: string|null}>  $asignaciones
     */
    public function sincronizarAsignaciones(int $compCodigo, array $asignaciones): void
    {
        ComponentePrograma::where('comp_codigo', $compCodigo)->delete();

        foreach ($asignaciones as $asig) {
            $proCodigo = (int) ($asig['pro_codigo'] ?? 0);
            if ($proCodigo <= 0) continue;
            $traCodigo = !empty($asig['tra_codigo']) ? (string) $asig['tra_codigo'] : null;

            ComponentePrograma::create([
                'comp_codigo' => $compCodigo,
                'pro_codigo' => $proCodigo,
                'tra_codigo' => $traCodigo,
            ]);
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
        if (($datos['tipos_publicacion'] ?? collect())->isEmpty()) {
            $faltantes[] = 'tipos de publicación';
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
