<?php

namespace App\Repositories;

use App\Models\Componente;
use App\Models\ComponentePrograma;
use App\Models\LapsoAcademico;
use App\Models\LineaInvestigacion;
use App\Models\MetodologiaInvestigacion;
use App\Models\TipoInvestigacion;
use App\Models\TipoPublicacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CatalogoRepository
{
    /**
     * @return array<string, Collection>
     */
    public function catalogos(?int $programaId = null): array
    {
        $ttl = now()->addMinutes(10);

        return [
            'lineas' => Cache::remember('gestion_cat_lineas', $ttl, fn() => $this->lineasActivas()),
            'metodologias' => Cache::remember('gestion_cat_metodologias', $ttl, fn() => $this->metodologiasActivas()),
            'tipos_publicacion' => Cache::remember('gestion_cat_tipos_publicacion', $ttl, fn() => $this->tiposPublicacionActivos()),
            'tipos_investigacion' => Cache::remember('gestion_cat_tipos_investigacion', $ttl, fn() => $this->tiposInvestigacionActivos()),
            'lapsos' => Cache::remember('gestion_cat_lapsos', $ttl, fn() => $this->lapsosActivos()),
            'componentes_disp' => $this->componentesGlobales(),
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

    public function lapsosActivos(): Collection
    {
        return LapsoAcademico::activos()->orderByDesc('lap_codigo')->get();
    }

    /**
     * Retorna todos los componentes activos (globales, sin filtro por programa).
     */
    public function componentesGlobales(): Collection
    {
        return Componente::where('estado_logico', true)
            ->orderBy('nombre')
            ->get();
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

        return $faltantes;
    }
}
