<?php

namespace App\Services;

use App\Models\Comunidad;
use App\Models\Direccion;
use App\Repositories\ComunidadRepository;

class ComunidadGestionService
{
    public function __construct(
        protected ComunidadRepository $comunidadRepo,
    ) {}

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
            'prefijo_telefono' => 'nullable|string|max:4',
            'numero_telefono' => 'nullable|string|max:15',
        ];
    }

    /**
     * Carga los datos de una comunidad para su edición.
     */
    public function cargarParaEdicion(int $id): array
    {
        $comunidad = $this->comunidadRepo->findWithDireccion($id);
        $direccion = $comunidad->direccion;

        return [
            'nombre' => $comunidad->nombre,
            'rif' => $comunidad->rif,
            'correo' => $comunidad->correo,
            'numero_telefono' => $comunidad->numero_telefono ?? '',
            'estado_id' => $direccion?->municipio?->est_codigo ? (string) $direccion->municipio->est_codigo : '',
            'municipio_id' => $direccion?->mun_codigo ? (string) $direccion->mun_codigo : '',
            'dir_nombre' => $direccion?->dir_calle ?? '',
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

        if (($datos['numero_telefono'] ?? '') !== '') {
            $payload['numero_telefono'] = ($datos['prefijo_telefono'] ?? '') . ($datos['numero_telefono'] ?? '');
        }

        $comunidad = $this->comunidadRepo->guardar($payload, $id);

        return $comunidad->getKey();
    }

    /**
     * Elimina una comunidad.
     */
    public function eliminar(int $id): void
    {
        $this->comunidadRepo->delete($id);
    }

    /**
     * Obtiene los datos para la vista de listado.
     */
    public function datosVistaListado(array $filtros, int $page): array
    {
        return [
            'comunidades' => $this->comunidadRepo->paginate($filtros, $page),
        ];
    }

    /**
     * Obtiene los datos necesarios para el formulario (catálogos externos).
     */
    public function datosVistaFormulario(?string $estadoId = null): array
    {
        $estados = $this->comunidadRepo->estados();
        $municipios = collect();

        if ($estadoId) {
            $municipios = $this->comunidadRepo->municipiosPorEstado((int) $estadoId);
        }

        return [
            'estados' => $estados,
            'municipios' => $municipios,
        ];
    }
}
