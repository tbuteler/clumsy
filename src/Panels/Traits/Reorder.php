<?php

namespace Clumsy\CMS\Panels\Traits;

use Clumsy\Assets\Facade as Asset;

trait Reorder
{
    public function beforeRenderReorder()
    {
        $this->setData('title', trans('clumsy::titles.reorder', ['resources' => $this->getLabelPlural()]));

        Asset::load('jquery-ui', 30);
    }
}
