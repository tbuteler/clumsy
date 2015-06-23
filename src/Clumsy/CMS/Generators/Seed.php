<?php namespace Clumsy\CMS\Generators;

class Seed extends Generator {

    public function targetName($template_data)
    {
        return array_get($template_data, 'object_name_plural').'Seeder';
    }
}