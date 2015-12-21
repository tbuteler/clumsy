<?php

namespace Clumsy\CMS\Panels\Traits;

trait Filterable
{
    public function prepareFilterable()
    {
        $this->setData('filterData', $this->generateFilterData());

        if ($this->isUsingFilters()) {
            $this->query->filtered($this->getParentModelId());
        }
    }

    public function filters()
    {
        return property_exists($this, 'filters') ? $this->filters : [];
    }

    public function isUsingFilters()
    {
        return (bool)$this->filters();
    }

    public function filterEquivalence()
    {
        $filterEquivalence = property_exists($this, 'filterEquivalence') ? $this->filterEquivalence : [];
        return array_merge($this->columnEquivalence(), $filterEquivalence);
    }

    public function generateFilterData()
    {
        if (empty($this->filters())) {
            return false;
        }

        $parent_id = $this->getParentModelId();

        $selected = [];
        $columns = [];
        $names = [];
        $identifier = $parent_id ? ".{$parent_id}" : null;
        $resourceName = $this->resourceName();
        $activeFilters = $this->session->get("clumsy.filter.{$resourceName}{$identifier}");

        $hasFilters = false;
        foreach ($this->filters() as $column) {

            $filterKey = str_contains($column, '.') ? last(explode('.', $column)) : $column;

            $equivalence = $this->filterEquivalence();
            $column = array_key_exists($column, $equivalence) ? array_pull($equivalence, $column) : $column;
            $columns[$column] = $filterKey;

            if (!is_null($activeFilters) && array_key_exists($column, $activeFilters)) {
                $hasFilters = true;
                $selected[$column] = $activeFilters[$column];
            } else {
                $selected[$column] = null;
            }

            // Names
            $names[$column] = $this->columnName($column);
        }

        $data = $this->model->getFilterData($columns, $this->query);

        return compact('data', 'selected', 'hasFilters', 'names');
    }
}
