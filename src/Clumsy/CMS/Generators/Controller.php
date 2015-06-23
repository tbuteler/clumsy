<?php namespace Clumsy\CMS\Generators;

class Controller extends Generator {

    public function targetName($template_data)
    {
        return array_get($template_data, 'object_name_plural').'Controller';
    }
}