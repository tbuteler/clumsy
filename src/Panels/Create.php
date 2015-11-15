<?php

namespace Clumsy\CMS\Panels;

use Clumsy\CMS\Panels\Traits\Editable;

class Create
{
    use Editable;

    protected $action = 'create';

    public function beforeRender()
    {
        $this->setData('title', trans('clumsy::titles.new_item', ['resource' => $this->getLabel()]));
    }
}
