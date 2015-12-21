<?php

namespace Clumsy\CMS\Generators;

class Controller extends Generator
{
    public function targetName()
    {
        return array_get($this->templateData, 'object_name_plural').'Controller';
    }
}
