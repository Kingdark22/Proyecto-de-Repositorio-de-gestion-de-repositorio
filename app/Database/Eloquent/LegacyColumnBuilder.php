<?php

namespace App\Database\Eloquent;

use App\Models\Concerns\MapsLegacyColumns;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * @mixin MapsLegacyColumns
 */
class LegacyColumnBuilder extends Builder
{
    // ───────────────────────────────────────────────────────────────
    //  WHERE helpers
    // ───────────────────────────────────────────────────────────────

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column instanceof Closure) {
            return parent::where($column, $operator, $value, $boolean);
        }

        if (is_array($column)) {
            return $this->whereNested(function ($query) use ($column) {
                foreach ($column as $key => $val) {
                    if (is_numeric($key)) {
                        $query->where($val);
                    } else {
                        $query->where($key, $val);
                    }
                }
            }, $boolean);
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        [$column, $operator, $value] = $this->model->qualifyLegacyWhere($column, $operator, $value);

        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * @param  string|array  $columns
     */
    public function whereNull($columns, $boolean = 'and', $not = false)
    {
        $columns = $this->mapColumns(Arr::wrap($columns));

        return parent::whereNull($columns, $boolean, $not);
    }

    /**
     * @param  string|array  $columns
     */
    public function whereNotNull($columns, $boolean = 'and')
    {
        $columns = $this->mapColumns(Arr::wrap($columns));

        return parent::whereNotNull($columns, $boolean);
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $legacyColumn = $column;
        $column = $this->model->mapLegacyColumn($column);

        if (is_array($values) && $legacyColumn !== $column) {
            $values = array_map(
                fn ($v) => $this->model->mapLegacyValueForQuery($legacyColumn, $v),
                $values
            );
        }

        return parent::whereIn($column, $values, $boolean, $not);
    }

    public function whereNotIn($column, $values, $boolean = 'and')
    {
        $legacyColumn = $column;
        $column = $this->model->mapLegacyColumn($column);

        if (is_array($values) && $legacyColumn !== $column) {
            $values = array_map(
                fn ($v) => $this->model->mapLegacyValueForQuery($legacyColumn, $v),
                $values
            );
        }

        return parent::whereNotIn($column, $values, $boolean);
    }

    public function whereBetween($column, $values, $boolean = 'and', $not = false)
    {
        $column = $this->model->mapLegacyColumn($column);

        return parent::whereBetween($column, $values, $boolean, $not);
    }

    public function whereNotBetween($column, $values, $boolean = 'and', $not = false)
    {
        $column = $this->model->mapLegacyColumn($column);

        return parent::whereNotBetween($column, $values, $boolean, $not);
    }

    public function whereDate($column, $operator, $value = null, $boolean = 'and')
    {
        [$column, $operator, $value] = $this->model->qualifyLegacyWhere($column, $operator, $value);

        return parent::whereDate($column, $operator, $value, $boolean);
    }

    public function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        [$column, $operator, $value] = $this->model->qualifyLegacyWhere($column, $operator, $value);

        return parent::whereMonth($column, $operator, $value, $boolean);
    }

    public function whereYear($column, $operator, $value = null, $boolean = 'and')
    {
        [$column, $operator, $value] = $this->model->qualifyLegacyWhere($column, $operator, $value);

        return parent::whereYear($column, $operator, $value, $boolean);
    }

    public function whereTime($column, $operator, $value = null, $boolean = 'and')
    {
        [$column, $operator, $value] = $this->model->qualifyLegacyWhere($column, $operator, $value);

        return parent::whereTime($column, $operator, $value, $boolean);
    }

    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        if (is_array($first)) {
            return parent::whereColumn($first, $operator, $second, $boolean);
        }

        $first = $this->model->mapLegacyColumn($first);

        if (func_num_args() === 2) {
            // whereColumn('col1', 'col2') — el segundo argumento es un nombre de columna, no operador
            $second = $this->model->mapLegacyColumn($operator);

            return parent::whereColumn($first, $second);
        }

        // whereColumn('col1', '=', 'col2') o con boolean
        $second = $this->model->mapLegacyColumn($second ?? $operator);

        return parent::whereColumn($first, $operator, $second, $boolean);
    }

    // ───────────────────────────────────────────────────────────────
    //  SELECT / columns
    // ───────────────────────────────────────────────────────────────

    /**
     * @param  array|mixed  $columns
     */
    public function select($columns = ['*'])
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        $columns = $this->mapColumns($columns);

        return parent::select($columns);
    }

    /**
     * @param  array|mixed  $column
     */
    public function addSelect($column)
    {
        $columns = is_array($column) ? $column : func_get_args();
        $columns = $this->mapColumns($columns);

        return parent::addSelect($columns);
    }

    // ───────────────────────────────────────────────────────────────
    //  ORDER BY
    // ───────────────────────────────────────────────────────────────

    public function orderBy($column, $direction = 'asc')
    {
        if ($column instanceof Closure) {
            return parent::orderBy($column, $direction);
        }

        $column = $this->model->mapLegacyColumn($column);

        return parent::orderBy($column, $direction);
    }

    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'desc');
    }

    public function orderByAsc($column)
    {
        return $this->orderBy($column, 'asc');
    }

    public function latest($column = null)
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn();
        }

        return $this->orderBy($column, 'desc');
    }

    public function oldest($column = null)
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn();
        }

        return $this->orderBy($column, 'asc');
    }

    // ───────────────────────────────────────────────────────────────
    //  GROUP BY / HAVING
    // ───────────────────────────────────────────────────────────────

    /**
     * @param  array|mixed  ...$groups
     */
    public function groupBy(...$groups)
    {
        foreach ($groups as $index => $group) {
            if (is_string($group)) {
                $groups[$index] = $this->model->mapLegacyColumn($group);
            } elseif (is_array($group)) {
                $groups[$index] = $this->mapColumns($group);
            }
        }

        return parent::groupBy(...$groups);
    }

    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $column = $this->model->mapLegacyColumn($column);

        return parent::having($column, $operator, $value, $boolean);
    }

    // ───────────────────────────────────────────────────────────────
    //  AGGREGATES
    // ───────────────────────────────────────────────────────────────

    public function pluck($column, $key = null)
    {
        $column = $this->model->mapLegacyColumn($column);

        if ($key !== null) {
            $key = $this->model->mapLegacyColumn($key);
        }

        return parent::pluck($column, $key);
    }

    public function value($column)
    {
        $column = $this->model->mapLegacyColumn($column);

        return parent::value($column);
    }

    public function min($column)
    {
        $column = $this->model->mapLegacyColumn($column);

        return parent::min($column);
    }

    public function max($column)
    {
        $column = $this->model->mapLegacyColumn($column);

        return parent::max($column);
    }

    public function avg($column)
    {
        $column = $this->model->mapLegacyColumn($column);

        return parent::avg($column);
    }

    public function sum($column)
    {
        $column = $this->model->mapLegacyColumn($column);

        return parent::sum($column);
    }

    // ───────────────────────────────────────────────────────────────
    //  UPDATE / DELETE / INSERT helpers
    // ───────────────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $values
     */
    public function update(array $values)
    {
        $values = $this->mapUpdateValues($values);

        return parent::update($values);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public function updateOrInsert(array $attributes, array $values = [])
    {
        $attributes = $this->mapUpdateValues($attributes);
        $values = $this->mapUpdateValues($values);

        return parent::updateOrInsert($attributes, $values);
    }

    public function increment($column, $amount = 1, array $extra = [])
    {
        $column = $this->model->mapLegacyColumn($column);
        $extra = $this->mapUpdateValues($extra);

        return parent::increment($column, $amount, $extra);
    }

    public function decrement($column, $amount = 1, array $extra = [])
    {
        $column = $this->model->mapLegacyColumn($column);
        $extra = $this->mapUpdateValues($extra);

        return parent::decrement($column, $amount, $extra);
    }

    // ───────────────────────────────────────────────────────────────
    //  ELOQUENT find / first helpers
    // ───────────────────────────────────────────────────────────────

    public function find($id, $columns = ['*'])
    {
        if (! is_array($id) && ! ($id instanceof \Illuminate\Contracts\Support\Arrayable)) {
            return $this->whereKey($id)->first($columns);
        }

        // findMany path
        return $this->findMany($id, $columns);
    }

    /**
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $ids
     */
    public function findMany($ids, $columns = ['*'])
    {
        $ids = $ids instanceof \Illuminate\Contracts\Support\Arrayable ? $ids->toArray() : $ids;
        $columns = $this->mapColumns(Arr::wrap($columns));

        if (count($columns) === 1 && $columns[0] === '*') {
            return parent::findMany($ids, $columns);
        }

        return $this->whereKey($ids)->get($columns);
    }

    public function findOrFail($id, $columns = ['*'])
    {
        $columns = $this->mapColumns(Arr::wrap($columns));

        $result = $this->find($id, $columns);

        if (is_array($id)) {
            return $result;
        }

        if (is_null($result)) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException)->setModel(
                get_class($this->model), $id
            );
        }

        return $result;
    }

    public function first($columns = ['*'])
    {
        $columns = $this->mapColumns(Arr::wrap($columns));

        return parent::first($columns);
    }

    public function firstOrFail($columns = ['*'])
    {
        $columns = $this->mapColumns(Arr::wrap($columns));

        return parent::firstOrFail($columns);
    }

    public function sole($columns = ['*'])
    {
        $columns = $this->mapColumns(Arr::wrap($columns));

        return parent::sole($columns);
    }

    public function chunkById($count, callable $callback, $column = null, $alias = null)
    {
        if ($column !== null) {
            $column = $this->model->mapLegacyColumn($column);
        }

        if ($alias !== null) {
            $alias = $this->model->mapLegacyColumn($alias);
        }

        return parent::chunkById($count, $callback, $column, $alias);
    }

    // ───────────────────────────────────────────────────────────────
    //  INTERNAL helpers
    // ───────────────────────────────────────────────────────────────

    /**
     * Mapea un array de nombres de columnas lógicas a físicas.
     * Respeta nombres con prefijo de tabla (ej. 'pro.pro_codigo').
     *
     * @param  list<string>  $columns
     * @return list<string>
     */
    protected function mapColumns(array $columns): array
    {
        return array_map(function (string $column): string {
            // No mapear si ya tiene prefijo de tabla (pro.pro_codigo) o es '*'
            if ($column === '*' || str_contains($column, '.')) {
                return $column;
            }

            return $this->model->mapLegacyColumn($column);
        }, $columns);
    }

    /**
     * Mapea las keys de un array asociativo (valores para update/insert).
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function mapUpdateValues(array $values): array
    {
        $mapped = [];

        foreach ($values as $key => $value) {
            $physical = str_contains($key, '.')
                ? $key
                : $this->model->mapLegacyColumn($key);

            // Mapear el valor también si hay un value map
            $mapped[$physical] = $key !== $physical
                ? $this->model->mapLegacyValueForQuery($key, $value)
                : $value;
        }

        return $mapped;
    }
}
