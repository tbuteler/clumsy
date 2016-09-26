<?php

namespace Clumsy\CMS\Panels;

use Clumsy\CMS\Panels\Traits\Reorder as ReorderTrait;

class Reorder extends Panel
{
    use ReorderTrait;

    protected $action = 'reorder';

    protected $items;

    public function getColumns($columns = null)
    {
        $columns = $this->reorderColumns() ? $this->reorderColumns() : array_slice($this->getBaseColumns(), 0, 1);

        if (method_exists($this, 'prepareColumns')) {
            $columns = $this->prepareColumns($columns);
        }

        return $columns;
    }

    public function loadItems()
    {
        if (!$this->itemsLoaded()) {
            $items = $this->query->orderBy($this->model->activeReorder(), 'asc')->get();
            $this->setItems($items);
            $this->setData('items', $items);
        }

        if ($this->model->isNested()) {
            $this->setData([
                'backLink' => $this->persistResourceOn($this->getParentModelEditUrl()),
            ]);
        }
    }

    public function setItems($items)
    {
        $this->items = $items;
        $this->itemsLoaded = true;

        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function reorderColumns()
    {
        return $this->getOptionalProperty('reorderColumns', false);
    }
}
