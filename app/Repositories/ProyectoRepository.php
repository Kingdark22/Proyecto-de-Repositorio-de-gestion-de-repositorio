<?php

namespace App\Repositories;

use App\Models\Proyecto;
use App\Models\ProyectoDocumento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ProyectoRepository
{
    protected array $relaciones = ['linea_investigacion', 'comunidad', 'metodologia', 'tipo_investigacion', 'documentos.componente'];

    public function find(int $id): ?Proyecto
    {
        return Proyecto::find($id);
    }

    public function findOrFail(int $id): Proyecto
    {
        return Proyecto::findOrFail($id);
    }

    public function findWithRelations(int $id): ?Proyecto
    {
        return Proyecto::with($this->relaciones)->find($id);
    }

    public function findWithDocuments(int $id): ?Proyecto
    {
        return Proyecto::with('documentos')->find($id);
    }

    /**
     * @param  array<int>  $ids
     */
    public function findWhereIn(string $column, array $ids): Collection
    {
        $result = Proyecto::with(['comunidad'])->whereIn($column, $ids)->get();
        Proyecto::precargarTitulos($result);
        return $result;
    }

    /**
     * @param  array<string>  $claves
     */
    public function findByEquipos(array $claves): Collection
    {
        if (empty($claves)) {
            return collect();
        }

        $result = Proyecto::with($this->relaciones)
            ->whereIn('pry_direccion_logica', $claves)
            ->get();

        Proyecto::precargarTitulos($result);
        return $result;
    }

    public function findFirstByEquipoRef(string $clave): ?Proyecto
    {
        return Proyecto::where('equipo_ref', $clave)->first();
    }

    /**
     * @param  array<int>  $ids
     */
    public function findLiderIds(array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        return Proyecto::whereIn('pry_direccion_logica', $ids)
            ->get()
            ->pluck('id');
    }

    /**
     * @param  array<int>  $claves
     */
    public function proyectosConEquipos(array $claves): Collection
    {
        return Proyecto::whereIn('pry_direccion_logica', $claves)
            ->get()
            ->keyBy('equipo_ref');
    }

    public function create(array $data): Proyecto
    {
        return Proyecto::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return Proyecto::whereKey($id)->update($data);
    }

    public function updateModel(Proyecto $proyecto, array $data): bool
    {
        return $proyecto->update($data);
    }

    public function delete(int $id): void
    {
        Proyecto::findOrFail($id)->delete();
    }

    public function alternarEstado(int $id): void
    {
        $item = Proyecto::findOrFail($id);
        $item->update(['pry_estado' => $item->pry_estado === 'Aprobado' ? 'Pendiente' : 'Aprobado']);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function paginate(array $filtros, int $page): LengthAwarePaginator
    {
        $paginator = Proyecto::with($this->relaciones)
            ->whereNotNull('pry_direccion_logica')
            ->where('pry_direccion_logica', '!=', '')
            ->when(($filtros['search'] ?? '') !== '', function ($q) use ($filtros) {
                $s = $filtros['search'];
                $q->where(function ($w) use ($s) {
                    // Resumen del proyecto
                    try {
                        $w->whereRaw('to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')) @@ plainto_tsquery(\'spanish\', ?)', [$s]);
                    } catch (\Throwable) {
                        $w->orWhereRaw('pry_resumen ILIKE ?', ['%' . $s . '%']);
                    }
                    // Nombre del grupo vía equipo_ref (identificador o grp_codigo)
                    $w->orWhereRaw('pry_direccion_logica ILIKE ?', ['%' . $s . '%']);
                    $w->orWhereRaw('EXISTS (SELECT 1 FROM grupo_proyecto_modulo g WHERE g.grp_identificador = proyectos.pry_direccion_logica AND g.grp_nombre ILIKE ?)', ['%' . $s . '%']);
                    $w->orWhereRaw('EXISTS (SELECT 1 FROM grupo_proyecto_modulo g WHERE g.grp_codigo::text = regexp_replace(proyectos.pry_direccion_logica, E\'^EQGRP:\', \'\') AND g.grp_nombre ILIKE ?)', ['%' . $s . '%']);
                    // Comunidad
                    $w->orWhereHas('comunidad', function ($qc) use ($s) {
                        $qc->whereRaw('com_nombre ILIKE ?', ['%' . $s . '%']);
                    });
                    // Línea de investigación
                    $w->orWhereHas('linea_investigacion', function ($ql) use ($s) {
                        $ql->whereRaw('lin_nombre_investigacion ILIKE ?', ['%' . $s . '%']);
                    });
                    // Metodología
                    $w->orWhereHas('metodologia', function ($qm) use ($s) {
                        $qm->whereRaw('mei_nombre ILIKE ?', ['%' . $s . '%']);
                    });
                    // Tipo de investigación
                    $w->orWhereHas('tipo_investigacion', function ($qt) use ($s) {
                        $qt->whereRaw('tin_nombre ILIKE ?', ['%' . $s . '%']);
                    });
                });
            })
            ->when(($filtros['estado'] ?? '') !== '', fn($q) => $q->where('pry_estado', $filtros['estado']))
            ->when(($filtros['comunidad'] ?? '') !== '', fn($q) => $q->where('comunidad_id', $filtros['comunidad']))
            ->when(($filtros['creador_cedula'] ?? '') !== '', fn($q) => $q->where('creador_cedula', $filtros['creador_cedula']))
            ->when(($filtros['equipo_ref'] ?? null) !== null, fn($q) => $q->whereIn('pry_direccion_logica', $filtros['equipo_ref']))
            ->latest()
            ->paginate(10, page: $page);

        // Pre-cargar títulos de grupos en UNA sola consulta (evita N+1)
        Proyecto::precargarTitulos($paginator->items());

        return $paginator;
    }

    /**
     * @return Collection<int, Proyecto>
     */
    public function pendientesValidacion(array $estados = ['Pendiente']): Collection
    {
        return Proyecto::whereIn('pry_estado', $estados)->get();
    }

    /**
     * @return Collection<int, Proyecto>
     */
    public function pendientesEstudiante(array $excludeEstados = ['Aprobado', 'Rechazado']): Collection
    {
        return Proyecto::where('actualizado_por_estudiante', false)
            ->whereNotIn('pry_estado', $excludeEstados)
            ->whereNotNull('pry_direccion_logica')
            ->get();
    }

    /**
     * @return Collection<int, Proyecto>
     */
    public function rechazados(): Collection
    {
        return Proyecto::where('pry_estado', 'Rechazado')
            ->whereNotNull('pry_direccion_logica')
            ->get();
    }

    /**
     * @return Collection<int, Proyecto>
     */
    public function conEquipoRefNotNull(): Collection
    {
        return Proyecto::whereNotNull('pry_direccion_logica')->get();
    }

    public function existeDocumento(int $proyectoId, int $compCodigo): bool
    {
        return ProyectoDocumento::where('pry_codigo', $proyectoId)
            ->where('comp_codigo', $compCodigo)
            ->exists();
    }

    public function findDocumentoByComp(int $proyectoId, int $compCodigo): ?ProyectoDocumento
    {
        return ProyectoDocumento::where('pry_codigo', $proyectoId)
            ->where('comp_codigo', $compCodigo)
            ->first();
    }

    public function crearDocumento(int $proyectoId, int $compCodigo, string $path): ProyectoDocumento
    {
        return ProyectoDocumento::create([
            'pry_codigo' => $proyectoId,
            'comp_codigo' => $compCodigo,
            'pd_archivo_path' => $path,
            'pd_orden' => 0,
        ]);
    }

    public function actualizarDocumento(int $id, array $data): bool
    {
        return ProyectoDocumento::whereKey($id)->update($data);
    }

    public function eliminarDocumentoViejo(string $path): void
    {
        Storage::disk('public')->delete($path);
    }
}
