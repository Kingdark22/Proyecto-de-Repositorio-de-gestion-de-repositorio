<?php

namespace App\Repositories;

use App\Models\Proyecto;
use App\Models\ProyectoDocumento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ProyectoRepository
{
    protected array $relaciones = ['tipo_publicacion', 'linea_investigacion', 'comunidad', 'metodologia', 'tipo_investigacion', 'documentos.componente'];

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
        return Proyecto::whereIn($column, $ids)->get();
    }

    /**
     * @param  array<string>  $claves
     */
    public function findByEquipos(array $claves): Collection
    {
        if (empty($claves)) {
            return collect();
        }

        return Proyecto::with($this->relaciones)
            ->whereIn('pry_direccion_logica', $claves)
            ->get();
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
        $item->update(['estado_logico' => !$item->estado_logico]);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function paginate(array $filtros, int $page): LengthAwarePaginator
    {
        return Proyecto::with($this->relaciones)
            ->when(($filtros['search'] ?? '') !== '', function ($q) use ($filtros) {
                $s = $filtros['search'];
                try {
                    $q->whereRaw('to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')) @@ plainto_tsquery(\'spanish\', ?)', [$s]);
                } catch (\Throwable) {
                    $q->whereRaw('pry_resumen ILIKE ?', ['%' . $s . '%']);
                }
            })
            ->when(($filtros['estado'] ?? '') !== '', fn($q) => $q->where('estado_validacion', $filtros['estado']))
            ->when(($filtros['comunidad'] ?? '') !== '', fn($q) => $q->where('comunidad_id', $filtros['comunidad']))
            ->when(($filtros['creador_cedula'] ?? '') !== '', fn($q) => $q->where('creador_cedula', $filtros['creador_cedula']))
            ->when(($filtros['equipo_ref'] ?? null) !== null, fn($q) => $q->whereIn('pry_direccion_logica', $filtros['equipo_ref']))
            ->latest()
            ->paginate(10, page: $page);
    }

    /**
     * @return Collection<int, Proyecto>
     */
    public function pendientesValidacion(array $estados = ['pendiente', 'completado']): Collection
    {
        return Proyecto::whereIn('estado_validacion', $estados)->get();
    }

    /**
     * @return Collection<int, Proyecto>
     */
    public function pendientesEstudiante(array $excludeEstados = ['aprobado', 'rechazado']): Collection
    {
        return Proyecto::where('actualizado_por_estudiante', false)
            ->whereNotIn('estado_validacion', $excludeEstados)
            ->whereNotNull('pry_direccion_logica')
            ->get();
    }

    /**
     * @return Collection<int, Proyecto>
     */
    public function rechazados(): Collection
    {
        return Proyecto::where('estado_validacion', 'rechazado')
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
