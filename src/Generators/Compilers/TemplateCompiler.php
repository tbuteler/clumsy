<?php

namespace Clumsy\CMS\Generators\Compilers;

class TemplateCompiler implements CompilerInterface
{
    /**
     * Compile the template using
     * the given data
     *
     * @param $template
     * @param $data
     * @return mixed
     */
    public function compile($template, $data)
    {
        foreach (array_dot($data) as $key => $value) {
            $template = str_replace('{{'.$key.'}}', $value, $template);
        }

        return $template;
    }
}
