<?php

namespace Clumsy\CMS\Generators;

class ViewsFolder extends Generator
{
    protected $psr4 = false;

    public function make()
    {
        return $this->makeFolder($this->getData('name'));
    }
}
