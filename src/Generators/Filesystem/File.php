<?php

namespace Clumsy\CMS\Generators\Filesystem;

use Illuminate\Filesystem\Filesystem;
use Clumsy\CMS\Generators\Compilers\TemplateCompiler;

class File
{
    protected $name;

    protected $data;

    protected $contents;

    /**
     * @param Filesystem $file
     * @param TemplateCompiler $compiler
     */
    public function __construct(Filesystem $file, TemplateCompiler $compiler)
    {
        $this->file = $file;

        $this->compiler = $compiler;
    }

    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    public function copyContentsFrom($origin, array $data = [])
    {
        $contents = $this->compiler->compile($this->file->get($origin), $data);

        $this->contents = $contents;

        return $this;
    }

    public function setContents($contents)
    {
        $this->contents = $contents;

        return $this;
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function make()
    {
        if ($this->exists()) {
            throw new FileAlreadyExists;
        }

        $filename = $this->file->name($this->name).'.'.$this->file->extension($this->name);
        $directory = str_replace($filename, '', $this->name);

        if (!$this->file->isDirectory($directory)) {
            $this->file->makeDirectory($directory, 0777, true);
        }

        return $this->file->put($this->name, $this->contents);
    }

    public function save()
    {
        $this->make();

        return $this;
    }

    public function exists()
    {
        return $this->file->exists($this->name);
    }
}
