<?php

namespace Clumsy\CMS\Models\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

trait Sortable
{
    public function hasSorter($column)
    {
        return method_exists($this, 'sort'.studly_case($column).'Column');
    }

    public function scopeOrderSortable($query, $column = null, $direction = 'asc')
    {
        $sorted = false;
        $resourceName = $this->resourceName();

        if (Session::has("clumsy.order.{$resourceName}")) {
            list($column, $direction) = Session::get("clumsy.order.{$resourceName}");

            if ($this->hasSorter($column)) {
                return $this->{'sort'.studly_case($column).'Column'}($query, $direction);
            }

            if (!in_array($column, Schema::getColumnListing($this->getTable()))) {
                Session::forget("clumsy.order.{$resourceName}");
            } else {
                $sorted = true;
            }
        } elseif ($column) {
            $sorted = true;
        }

        if (!$sorted) {
            if (count($this->defaultOrder)) {
                foreach ($this->defaultOrder as $column => $direction) {
                    // If current row is not associative, rebuild variables
                    if (is_numeric($column)) {
                        $column = $direction;
                        $direction = 'asc';
                    }

                    $query->orderBy($column, $direction);
                }

                return $query;
            } else {
                $column = config('clumsy.cms.default-order.column');
                $direction = config('clumsy.cms.default-order.direction');
            }
        }

        if ($column && $direction) {
            return $query->orderBy($column, $direction);
        }

        return $query;
    }

    public function sortChildrenCount($query, $childModel, $direction)
    {
        if (!is_object($childModel)) {
            $childModel = new $childModel;
        }

        $parentIdColumn = $childModel->parentIdColumn();
        $childKey = $childModel->getKeyName();
        $parentTableAndKey = $this->getTable().'.'.$this->getKeyName();

        $orderQuery = $childModel->select($parentIdColumn, DB::raw("count(`{$childKey}`) as `quantity`"))
                                 ->groupBy($parentIdColumn)
                                 ->toSql();

        return $query->leftJoin(DB::raw("($orderQuery) as orderQuery"), "orderQuery.{$parentIdColumn}", '=', $parentTableAndKey)
                     ->orderBy('orderQuery.quantity', $direction);
    }
}
