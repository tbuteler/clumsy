<?php

namespace Clumsy\CMS\Panels\Traits;

trait Reorderable
{
    public function prepareReorderable()
    {
        $reorderUrl = $this->isChild() ? $this->persistResourceOn($this->getParentModelEditUrl(), 'reorder') : false;

        $this->setData([
            'reorder'    => $this->model->activeReorder(),
            'reorderUrl' => $reorderUrl,
        ]);
    }
}
