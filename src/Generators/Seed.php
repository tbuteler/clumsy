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
        return $this->getData('objectNamePlural').'Seeder';
    }

    public function makeFile(File $file = null)
    {
        parent::makeFile();

        $file = App::make(File::class);
        $stub = __DIR__.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'factory.stub';
        $file->copyContentsFrom($stub, $this->getData());

        Filesystem::append(database_path('/factories/ModelFactory.php'), $file->getContents());
    }
}
