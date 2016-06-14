<?php

namespace Clumsy\CMS\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Clumsy\CMS\Generators\Filesystem\FileAlreadyExists;

/**
 * Publish a Pivot Trait for arbitrary resource
 *
 * @author Tomas Buteler <tbuteler@gmail.com>
 */
class PivotCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'clumsy:pivot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes a Trait for creating a pivot relationship with a given resource';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->generate('pivot-trait');
    }

    protected function getTemplateData()
    {
        return array_merge(parent::getTemplateData(), [
            'model_namespace' => config("clumsy.cms.model-namespace"),
        ]);
    }

    protected function getArguments()
    {
        return [
            ['resource_name', InputArgument::REQUIRED, 'The name of the Clumsy resource to be pivoted']
        ];
    }
}
