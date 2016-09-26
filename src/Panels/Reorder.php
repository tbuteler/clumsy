<?php

namespace Clumsy\CMS\Panels;

use Clumsy\CMS\Panels\Traits\Panel;

class Reorder
{
    use Panel {
        Panel::getColumns as getPanelColumns;
    }

    protected $action = 'reorder';

    protected $items;

    public function getColumns()
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
