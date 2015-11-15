<?php

namespace Clumsy\CMS\Panels\Traits;

trait Sortable
{
    public function prepareSortable()
    {
        $this->setData([
            'orderEquivalence' => $this->orderEquivalence(),
            'sortable'         => $this->isSortable(),
        ]);

        if ($this->isSortable()) {
            $this->query->orderSortable();
        }
    }

    public function isSortable()
    {
        return property_exists($this, 'sortable') ? $this->sortable : true;
    }

    public function orderEquivalence()
    {
        $orderEquivalence = property_exists($this, 'orderEquivalence') ? $this->orderEquivalence : [];
        return array_merge($this->columnEquivalence(), $orderEquivalence);
    }
}
