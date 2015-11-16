<?php

namespace Clumsy\CMS\Generators;

class Panel extends Generator
{
    public function getConfigNamespace()
    {
        return config('clumsy.cms.panel-namespace');
    }

    public function getNamespace()
    {
        return parent::getNamespace().'\\'.$this->getData('object_name_plural');
    }
}