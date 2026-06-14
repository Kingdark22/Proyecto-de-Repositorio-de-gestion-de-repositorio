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

        $logicalColumn = null;
        if (array_key_exists('activo', $schema)) {
            $logicalColumn = 'activo';
        } elseif (array_key_exists('estado_logico', $schema)) {
            $logicalColumn = 'estado_logico';
        } else {
            $logicalColumn = property_exists($this, 'statusColumn') ? $this->statusColumn : 'activo';
        }

        $physicalColumn = $schema[$logicalColumn] ?? $logicalColumn;
        $newValue = !$this->$logicalColumn;

        // Map boolean value through schema's value map (e.g., PG enums)
        $values = config("repositorio_schema.{$this->getTable()}.values.{$logicalColumn}", []);
        if (! empty($values)) {
            $newValue = $values[(int) $newValue] ?? $newValue;
        }

        return $this->update([$physicalColumn => $newValue]);
    }

    /**
     * Elimina el registro.
     */
    public function borrar(): ?bool
    {
        return $this->delete();
    }
}
