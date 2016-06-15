<?php

namespace Clumsy\CMS\Generators;

class Panel extends Generator
{
    public function getConfigNamespace($slug = null)
    {
        return config('clumsy.cms.panel-namespace');
    }

    public function getNamespace($slug = null)
    {
        return parent::getNamespace().'\\'.$this->getData('objectName');
    }
}
