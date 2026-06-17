<?php

namespace App\Database\Eloquent;

use App\Models\Concerns\MapsLegacyColumns;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin MapsLegacyColumns
 */
class LegacyColumnBuilder extends Builder
{
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

    public function orderBy($column, $direction = 'asc')
    {
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
}
