<?php

namespace Clumsy\CMS\Generators;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File as Filesystem;
use Clumsy\CMS\Generators\Filesystem\File;

class Seed extends Generator
{
    protected $psr4 = false;

    public function targetName()
    {
        return $this->getData('object_name_plural').'Seeder';
    }

    public function makeFile(File $file = null)
    {
        $modelClass = $this->getNamespace('model').'\\'.$this->getData('object_name');
        $this->setData('model', $modelClass);

        $file = App::make(File::class);
        $stub = __DIR__.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'factory.stub';
        $file->copyContentsFrom($stub, $this->getData());

        Filesystem::append(database_path('/factories/ModelFactory.php'), $file->getContents());

        parent::makeFile();
    }
}
