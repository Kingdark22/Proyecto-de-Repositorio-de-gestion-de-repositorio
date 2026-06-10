<?php

namespace App\Models\Concerns;

use App\Database\Eloquent\LegacyColumnBuilder;
use Illuminate\Database\Eloquent\Builder;

trait MapsLegacyColumns
{
    public function newEloquentBuilder($query): Builder
    {
        return new LegacyColumnBuilder($query);
    }

    public function getKeyName(): string
    {
        return config("repositorio_schema.{$this->getTable()}.primary_key")
            ?? parent::getKeyName();
    }

    public static function legacyColumnMap(): array
    {
        $table = (new static)->getTable();

        return config("repositorio_schema.{$table}.columns", []);
    }

    public static function legacyValueMap(): array
    {
        $table = (new static)->getTable();

        return config("repositorio_schema.{$table}.values", []);
    }

    public function mapLegacyColumn(string $column): string
    {
        return static::legacyColumnMap()[$column] ?? $column;
    }

    public function qualifyColumn($column): string
    {
        return parent::qualifyColumn($this->mapLegacyColumn($column));
    }

    /**
     * @return array{0: string, 1: string, 2: mixed}
     */
    public function qualifyLegacyWhere($column, $operator = null, $value = null): array
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $legacyKey = $column;
        $column = $this->mapLegacyColumn($column);
        $value = $this->mapLegacyValueForQuery($legacyKey, $value);

        return [$column, $operator, $value];
    }

    public function mapLegacyValueForQuery(string $legacyColumn, mixed $value): mixed
    {
        $map = static::legacyValueMap()[$legacyColumn] ?? null;

        if ($map === null) {
            return $value;
        }

        if (is_string($value)) {
            $lower = strtolower($value);

            return $map[$lower] ?? $map[$value] ?? $value;
        }

        return $map[$value] ?? $value;
    }

    public function unmapLegacyValue(string $legacyColumn, mixed $value): mixed
    {
        $map = static::legacyValueMap()[$legacyColumn] ?? null;

        if ($map === null || $value === null) {
            return $value;
        }

        $flipped = array_flip($map);

        return $flipped[$value] ?? $value;
    }

    public function getAttribute($key): mixed
    {
        $physical = static::legacyColumnMap()[$key] ?? null;

        if ($physical !== null && $physical !== $key) {
            $value = parent::getAttribute($physical);

            return $this->unmapLegacyValue($key, $value);
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value): mixed
    {
        $physical = static::legacyColumnMap()[$key] ?? null;

        if ($physical !== null && $physical !== $key) {
            $value = $this->mapLegacyValueForQuery($key, $value);

            return parent::setAttribute($physical, $value);
        }

        return parent::setAttribute($key, $value);
    }

}
