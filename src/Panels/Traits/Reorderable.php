<?php

namespace Clumsy\CMS\Panels\Traits;

use Clumsy\Assets\Facade as Asset;

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

    public function beforeRenderReorderable()
    {
        $this->setData('title', trans('clumsy::titles.reorder', ['resources' => $this->panel->getLabelPlural()]));

        Asset::load('jquery-ui', 30);
    }
}
