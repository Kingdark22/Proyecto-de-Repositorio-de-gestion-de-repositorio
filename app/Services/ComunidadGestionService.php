<?php

namespace App\Services;

use App\Models\Comunidad;
use App\Models\ComunidadContacto;
use App\Models\Direccion;
use App\Models\Estado;
use App\Models\Municipio;
use Illuminate\Support\Facades\Cache;

class ComunidadGestionService
{
    /**
     * Reglas de validación para el formulario de comunidades.
     */
    public function reglasValidacion(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'rif' => 'nullable|string|max:50',
            'estado_id' => 'required|integer|exists:estados,est_codigo',
            'municipio_id' => 'required|integer|exists:municipios,mun_codigo',
            'dir_nombre' => 'required|string|max:500',
            'correo' => 'nullable|email|max:150',
        ];
    }

    /**
     * Carga los datos de una comunidad para su edición.
     */
    public function cargarParaEdicion(int $id): array
    {
        $comunidad = Comunidad::with(['direccion.municipio.estado', 'contactos'])->whereKey($id)->firstOrFail();

        $direccion = $comunidad->direccion;

        return [
            'nombre' => $comunidad->nombre,
            'rif' => $comunidad->rif,
            'correo' => $comunidad->correo,
            'estado_id' => $direccion?->municipio?->est_codigo ? (string) $direccion->municipio->est_codigo : '',
            'municipio_id' => $direccion?->mun_codigo ? (string) $direccion->mun_codigo : '',
            'dir_nombre' => $direccion?->dir_calle ?? '',
            'contactos' => $comunidad->contactos->map(fn ($c) => [
                'nombre' => $c->ccon_nombre ?? '',
                'apellido' => $c->ccon_apellido ?? '',
                'correo' => $c->ccon_correo ?? '',
                'telefono' => $c->ccon_telefono ?? '',
                'cargo' => $c->ccon_cargo ?? '',
            ])->toArray(),
        ];
    }

    /**
     * Guarda o actualiza una comunidad.
     */
    public function guardar(?int $id, array $datos): int
    {
        $dirNombre = trim($datos['dir_nombre'] ?? '');

        if ($dirNombre !== '' && !empty($datos['municipio_id'])) {
            $direccion = Direccion::firstOrCreate(
                ['dir_calle' => $dirNombre, 'mun_codigo' => $datos['municipio_id'], 'dir_parroquia' => '', 'dir_sector' => '']
            );
            $direccionId = $direccion->dir_codigo;
        } else {
            $direccionId = null;
        }

        $payload = [
            'nombre' => $datos['nombre'],
            'rif' => $datos['rif'],
            'correo' => $datos['correo'],
            'direccion_id' => $direccionId,
        ];

        $comunidad = Comunidad::guardar($payload, $id);

        // Save contactos
        $contactos = $datos['contactos'] ?? [];
        if (!empty($contactos)) {
            ComunidadContacto::where('com_codigo', $comunidad->getKey())->delete();
            $toInsert = [];
            foreach ($contactos as $c) {
                $toInsert[] = [
                    'com_codigo' => $comunidad->getKey(),
                    'ccon_nombre' => $c['nombre'] ?? '',
                    'ccon_apellido' => $c['apellido'] ?? '',
                    'ccon_correo' => $c['correo'] ?? '',
                    'ccon_telefono' => ($c['prefijo'] ?? '') . ($c['telefono'] ?? ''),
                    'ccon_cargo' => $c['cargo'] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            ComunidadContacto::insert($toInsert);
        }

        return $comunidad->getKey();
    }

    /**
     * Elimina una comunidad.
     */
    public function eliminar(int $id): void
    {
        $comunidad = Comunidad::findOrFail($id);
        $direccionId = $comunidad->direccion_id;
        $comunidad->delete();
        if ($direccionId) {
            Direccion::where('dir_codigo', $direccionId)->whereDoesntHave('comunidad')->delete();
        }
    }

    /**
     * Obtiene los datos para la vista de listado.
     */
    public function datosVistaListado(array $filtros, int $page): array
    {
        $termino = trim($filtros['search'] ?? '');

        $comunidades = Comunidad::with('direccion.municipio.estado')
            ->when($termino !== '', function ($q) use ($termino) {
                $q->where('nombre', 'ILIKE', '%' . $termino . '%')
                    ->orWhere('rif', 'ILIKE', '%' . $termino . '%');
            })
            ->orderByDesc((new Comunidad())->getKeyName())
            ->paginate(10, ['*'], 'page', $page);

        return [
            'comunidades' => $comunidades,
        ];
    }

    /**
     * Obtiene los datos necesarios para el formulario (catálogos externos).
     */
    public function datosVistaFormulario(?string $estadoId = null): array
    {
        $estados = Cache::remember('comunidad_estados', 86400, fn() => Estado::orderBy('est_nombre')->get());
        $municipios = collect();

        if ($estadoId) {
            $municipios = Municipio::where('est_codigo', $estadoId)->orderBy('mun_nombre')->get();
        }

        return [
            'estados' => $estados,
            'municipios' => $municipios,
        ];
    }
}
