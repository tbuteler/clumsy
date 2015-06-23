<?php namespace Clumsy\CMS\Generators;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Clumsy\CMS\Generators\Filesystem\Filesystem;
use Clumsy\CMS\Generators\Compilers\TemplateCompiler;

abstract class Generator {

    /**
     * @var Filesystem
     */
    protected $file;

    /**
     * @var TemplateCompiler
     */
    protected $compiler;

    /**
     * @param Filesystem $file
     * @param TemplateCompiler $compiler
     */
    public function __construct(Filesystem $file, TemplateCompiler $compiler)
    {
        $this->file = $file;

        $this->compiler = $compiler;
    }

    public function getSlug()
    {
        return snake_case(class_basename($this));
    }

    public function getNamespace()
    {
        return Config::get('clumsy::'.$this->getSlug().'_namespace');
    }

    public function origin()
    {
        return __DIR__.'/stubs/'.$this->getSlug().'.stub';
    }

    public function targetBase()
    {
        $namespace = $this->getNamespace() ? '/'.str_replace('\\', '/', $this->getNamespace()) : '';

        return Config::get('clumsy::'.$this->getSlug().'_path').$namespace;
    }

    public function targetName($template_data)
    {
        return array_get($template_data, 'object_name');
    }

    public function targetFileName($template_data)
    {
        return $this->targetName($template_data).'.php';
    }

    public function targetFile($template_data)
    {
        return $this->targetBase().'/'.$this->targetFileName($template_data);
    }

    public function targetFolder($template_data)
    {
        return $this->targetBase().'/'.$this->targetName($template_data);
    }

    /**
     * Compile the file
     *
     * @param $templatePath
     * @param array $data
     * @return mixed
     */
    protected function compile(array $data)
    {
        return $this->compiler->compile($this->file->get($this->origin()), $data);
    }

    protected function makeFile($template_data)
    {
        $template_data = array_merge(
            $template_data,
            array(
                'namespace' => $this->getNamespace() ? ' namespace '.$this->getNamespace().';' : '',
            )
        );

        // We first need to compile the template,
        // according to the data that we provide.
        $template = $this->compile($template_data);

        // Now that we have the compiled template,
        // we can actually generate the file.
        $this->file->make($this->targetFile($template_data), $template);
    }

    protected function makeFolder($template_data)
    {
        if (!File::exists($this->targetFolder($template_data)))
        {
            File::makeDirectory($this->targetFolder($template_data), 0777, true);
        }
    }

    /**
     * Run the generator
     *
     * @param $templatePath
     * @param $template_data
     * @param $filePathToGenerate
     */
    public function make($template_data)
    {
        return $this->makeFile($template_data);
    }
}
