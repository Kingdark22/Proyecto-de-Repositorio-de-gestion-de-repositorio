<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class UnicidadNombreService
{
    public function check(
        string $modelClass,
        string $field,
        mixed $value,
        ?int $excludeId = null,
        array $extraConditions = [],
    ): bool {
        if (!is_subclass_of($modelClass, Model::class)) {
            return true;
        }

        $instance = new $modelClass;
        $table = $instance->getTable();

        if (method_exists($instance, 'mapLegacyColumn')) {
            $field = $instance->mapLegacyColumn($field);
        }

        $query = $modelClass::whereRaw("{$table}.{$field} ILIKE ?", [trim((string) $value)]);

        if ($excludeId !== null) {
            $keyName = $instance->getKeyName();
            $query->where($keyName, '!=', $excludeId);
        }

        foreach ($extraConditions as $col => $val) {
            $query->where($col, $val);
        }

        return !$query->exists();
    }
}
