<?php

namespace Clumsy\CMS\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

trait Filterable
{
    public function hasFilterer($column)
    {
        return method_exists($this, 'filter'.studly_case(str_replace('.', '_', $column)).'Column');
    }

    public function scopeFiltered(Builder $query, $parentId = null)
    {
        $resourceName = $this->resourceName();
        $identifier = $parentId ? ".{$parentId}" : null;
        if (Session::has("clumsy.filter.{$resourceName}{$identifier}")) {
            $activeFilter = Session::get("clumsy.filter.{$resourceName}{$identifier}");
            foreach ($activeFilter as $column => $values) {

                if ($this->hasFilterer($column)) {
                    return $this->{'filter'.studly_case(str_replace('.', '_', $column)).'Column'}($query, $values);
                }

                if (in_array($column, Schema::getColumnListing($this->getTable()))) {
                    // If the column exists in the table, use it
                    $query->where(function (Builder $query) use ($values, $column) {

                        $i = 0;
                        foreach ($values as $item) {
                            $where = $i === 0 ? 'where' : 'orWhere';
                            $query->$where($column, $item);
                            $i++;
                        }
                    });
                } else {

                    // Otherwise, assume it's a nested filter and
                    // look for the column in the child model
                    list($model, $newColumn) = explode('.', $column);
                    $query->whereHas($model, function (Builder $query) use ($newColumn, $values) {

                        $query->where(function (Builder $query) use ($values, $newColumn) {

                            $i = 0;
                            foreach ($values as $item) {
                                $where = $i === 0 ? 'where' : 'orWhere';
                                $query->$where($newColumn, $item);
                                $i++;
                            }
                        });
                    });
                }
            }
        }
    }

    public function getFilterData($columns, Builder $query)
    {
        $data = [];

        foreach ($columns as $column => $filterKey) {

            $values = [];

            $queryaux = clone $query;

            // Remove eager loads from query
            $queryaux->setEagerLoads([]);

            $index = $column;

            if (in_array($column, Schema::getColumnListing($this->getTable()))) {
                // If the column exists in the table, use it
                $items = $queryaux->select($column)->distinct()->get();
            } elseif (!str_contains($column, '.')) {

                throw new InvalidArgumentException("Filter column [{$column}] not found on [".$this->getTable()."] database table. Use the 'columnEquivalence' property on the filterable panel when filtering with accessors.");

            } else {
                // Otherwise, assume it's a nested filter and look for the column in the relationship
                list($model, $column) = explode('.', $column);
                $items = $this->$model()->getModel()->select($column)->distinct()->get();
            }

            // If the column is a boolean, use 'yes' or 'no' values
            if (in_array($filterKey, $this->booleans()) && !$this->hasGetMutator($filterKey)) {
                $items->each(function (Eloquent $item) use ($column, &$values) {
                    $attributes = $item->getAttributes();
                    $values[$attributes[$column]] = $item->$column == 1 ? trans('clumsy::fields.yes') : trans('clumsy::fields.no');
                });
            } else {
                // Otherwise, use the default attribute (will use get mutator, if available)
                $items->each(function (Eloquent $item) use ($column, $filterKey, &$values) {
                    $attributes = $item->getAttributes();
                    if ($attributes[$column]) {
                        $values[$attributes[$column]] = $item->$filterKey;
                    }
                });
            }

            asort($values);

            $data[$index] = $values;
        }

        return $data;
    }
}
