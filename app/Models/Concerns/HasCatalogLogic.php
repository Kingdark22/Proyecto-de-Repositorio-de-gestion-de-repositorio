<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasCatalogLogic
{
    /**
     * Guarda o actualiza un registro del catálogo.
     */
    public static function guardar(array $datos, ?int $id = null): self
    {
        if ($id === null) {
            return self::create($datos);
        }

        $model = self::query()->whereKey($id)->first();

        if (! $model) {
            $model = new self();
            $model->setAttribute($model->getKeyName(), $id);
        }

        $model->fill($datos);
        $model->save();

        return $model;
    }

    /**
     * Alterna el estado del registro (activo o estado_logico).
     */
    public function alternarEstado(): bool
    {
        $schema = config("repositorio_schema.{$this->getTable()}.columns", []);

        if (array_key_exists('activo', $schema)) {
            return $this->update(['activo' => !$this->activo]);
        }

        if (array_key_exists('estado_logico', $schema)) {
            return $this->update(['estado_logico' => !$this->estado_logico]);
        }

        $coluna = property_exists($this, 'statusColumn') ? $this->statusColumn : 'activo';
        return $this->update([$coluna => !$this->$coluna]);
    }

    /**
     * Elimina el registro.
     */
    public function borrar(): ?bool
    {
        return $this->delete();
    }
}
