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
        return $this->getOptionalProperty('sortable', true);
    }

    public function orderEquivalence()
    {
        $orderEquivalence = $this->getOptionalProperty('orderEquivalence', []);
        return array_merge($this->columnEquivalence(), $orderEquivalence);
    }
}
