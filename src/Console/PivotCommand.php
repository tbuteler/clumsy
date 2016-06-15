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
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clumsy:pivot
                            {resource : The name of the resource to pivot}
                            {--pivot=* : In order to create a pivot table migration, specify the related resource(s)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish a Trait for creating a pivot relationship with a given resource';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $resource = $this->getResourceSlug();
        $this->parsePivots();

        $this->generate($resource, 'pivot-trait');

        foreach ($this->pivotResources as $pivot) {
            $resources = [
                $resource,
                $pivot,
            ];
            asort($resources);
            $this->generate($resource, 'migration-pivot', [
                'a' => $this->generateTemplateData(array_shift($resources)),
                'b' => $this->generateTemplateData(array_shift($resources)),
            ]);
        }
    }
}
