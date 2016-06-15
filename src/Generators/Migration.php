<?php

namespace Clumsy\CMS\Generators;

use Illuminate\Support\Composer;

abstract class Migration extends Generator
{
    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    public function targetBase()
    {
        return database_path('migrations');
    }

    public function make()
    {
        parent::make();

        $this->composer->dumpAutoloads();
    }
}
