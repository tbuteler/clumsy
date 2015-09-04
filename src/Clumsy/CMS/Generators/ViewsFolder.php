<?php
namespace Clumsy\CMS\Generators;

class ViewsFolder extends Generator
{
    public function targetName($template_data)
    {
        return array_get($template_data, 'plural');
    }

    public function make($template_data)
    {
        return $this->makeFolder($template_data);
    }
}
