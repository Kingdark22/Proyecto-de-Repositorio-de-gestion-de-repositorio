<?php

namespace App\Repositories;

use App\Models\Comunidad;
use App\Models\Direccion;
use App\Models\Estado;
use App\Models\Municipio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ComunidadRepository
{
    public function find(int $id): ?Comunidad
    {
        return Comunidad::find($id);
    }

    public function findOrFail(int $id): Comunidad
    {
        return Comunidad::findOrFail($id);
    }

    public function findWithDireccion(int $id): Comunidad
    {
        return Comunidad::with(['direccion.municipio.estado'])->whereKey($id)->firstOrFail();
    }

    /**
     * @return Collection<int, Comunidad>
     */
    public function allOrdered(): Collection
    {
        return Cache::remember('gestion_comunidades_ordenadas', now()->addMinutes(10), fn() =>
            Comunidad::orderBy('nombre')->get(['com_codigo', 'com_nombre'])
        );
    }

    /**
     * @return Collection<int, Comunidad>
     */
    public function paginate(array $filtros, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $termino = trim($filtros['search'] ?? '');

        return Comunidad::with('direccion.municipio.estado')
            ->when($termino !== '', function ($q) use ($termino) {
                $q->where('nombre', 'ILIKE', '%' . $termino . '%')
                    ->orWhere('rif', 'ILIKE', '%' . $termino . '%');
            })
            ->orderByDesc((new Comunidad())->getKeyName())
            ->paginate(10, ['*'], 'page', $page);
    }

    public function estados(): Collection
    {
        return Cache::remember('comunidad_estados', 86400, fn() =>
            Estado::orderBy('est_nombre')->get()
        );
    }

    public function municipiosPorEstado(int $estadoId): Collection
    {
        return Cache::remember('comunidad_municipios_estado_' . $estadoId, 86400, fn() =>
            Municipio::where('est_codigo', $estadoId)->orderBy('mun_nombre')->get()
        );
    }

    public function guardar(array $payload, ?int $id = null): Comunidad
    {
        return Comunidad::guardar($payload, $id);
    }

    public function delete(int $id): void
    {
        $comunidad = $this->findOrFail($id);
        $direccionId = $comunidad->direccion_id;
        $comunidad->delete();

        if ($direccionId) {
            Direccion::where('dir_codigo', $direccionId)
                ->whereDoesntHave('comunidad')
                ->delete();
        }
    }
}
