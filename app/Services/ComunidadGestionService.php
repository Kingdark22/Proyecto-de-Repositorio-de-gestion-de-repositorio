<?php

namespace App\Services;

use App\Models\Comunidad;
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
            'prefijo_telefono' => 'nullable|in:0424,0414,0412,0422,0416,0426',
            'numero_telefono' => 'nullable|digits:7',
            'contactos' => 'nullable|array',
            'contactos.*.nombre' => 'required|string|max:255',
            'contactos.*.apellido' => 'nullable|string|max:255',
            'contactos.*.correo' => 'nullable|email|max:150',
            'contactos.*.correo_confirmacion' => 'nullable|email|max:150',
            'contactos.*.prefijo' => 'nullable|in:0424,0414,0412,0422,0416,0426',
            'contactos.*.telefono' => 'nullable|string|max:50',
            'contactos.*.cargo' => 'nullable|string|max:100',
        ];
    }

    /**
     * Carga los datos de una comunidad para su edición.
     */
    public function cargarParaEdicion(int $id): array
    {
        $comunidad = Comunidad::with('contactos', 'direccion.municipio.estado')->whereKey($id)->firstOrFail();

        $direccion = $comunidad->direccion;

        return [
            'nombre' => $comunidad->nombre,
            'rif' => $comunidad->rif,
            'correo' => $comunidad->correo,
            'numero_telefono' => $comunidad->numero_telefono,
            'estado_id' => $direccion?->municipio?->est_codigo ? (string) $direccion->municipio->est_codigo : '',
            'municipio_id' => $direccion?->mun_codigo ? (string) $direccion->mun_codigo : '',
            'dir_nombre' => $direccion?->dir_calle ?? '',
            'contactos' => $comunidad->contactos->map(fn ($c) => [
                'nombre' => $c->ccon_nombre,
                'apellido' => $c->ccon_apellido ?? '',
                'correo' => $c->ccon_correo ?? '',
                'correo_confirmacion' => $c->ccon_correo ?? '',
                'prefijo' => $c->ccon_telefono ? (strlen(trim($c->ccon_telefono)) >= 10 ? substr(trim($c->ccon_telefono), 0, 4) : '0424') : '0424',
                'telefono' => $c->ccon_telefono ? (strlen(trim($c->ccon_telefono)) >= 7 ? substr(trim($c->ccon_telefono), -7) : trim($c->ccon_telefono)) : '',
                'cargo' => array_key_exists($c->ccon_cargo ?? '', config('comunidades.cargos_contacto', [])) ? ($c->ccon_cargo ?? '') : ($c->ccon_cargo ?? ''),
                'mostrar_input_cargo' => !array_key_exists($c->ccon_cargo ?? '', config('comunidades.cargos_contacto', [])),
                'cargo_custom' => array_key_exists($c->ccon_cargo ?? '', config('comunidades.cargos_contacto', [])) ? '' : ($c->ccon_cargo ?? ''),
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
            'numero_telefono' => !empty($datos['prefijo_telefono']) && !empty($datos['numero_telefono'])
                ? $datos['prefijo_telefono'] . $datos['numero_telefono']
                : ($datos['numero_telefono'] ?? null),
            'dir_codigo' => $direccionId,
            'tipo' => 'Consejo comunal',
        ];

        $comunidad = Comunidad::guardar($payload, $id);

        if (isset($datos['contactos'])) {
            $comunidad->contactos()->delete();
            $rows = [];
            foreach ($datos['contactos'] as $contacto) {
                $prefijo = $contacto['prefijo'] ?? '';
                $numero = $contacto['telefono'] ?? '';
                $telefono = $numero !== '' ? ($prefijo !== '' ? $prefijo . $numero : $numero) : '';
                $rows[] = [
                    'ccom_codigo' => $comunidad->getKey(),
                    'ccon_nombre' => $contacto['nombre'],
                    'ccon_apellido' => $contacto['apellido'] ?? null,
                    'ccon_correo' => $contacto['correo'] ?? null,
                    'ccon_telefono' => $telefono,
                    'ccon_cargo' => $contacto['cargo'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($rows) {
                $comunidad->contactos()->insert($rows);
            }
        }

        return $comunidad->getKey();
    }

    /**
     * Elimina una comunidad.
     */
    public function eliminar(int $id): void
    {
        $comunidad = Comunidad::findOrFail($id);
        $direccionId = $comunidad->dir_codigo;
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

        $comunidades = Comunidad::with('contactos', 'direccion.municipio.estado')
            ->when($termino !== '', function ($q) use ($termino) {
                $q->where('nombre', 'like', '%' . $termino . '%')
                    ->orWhere('rif', 'like', '%' . $termino . '%');
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
