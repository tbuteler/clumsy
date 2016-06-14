<?php

namespace Clumsy\CMS\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Clumsy\CMS\Generators\Filesystem\FileAlreadyExists;

/**
 * Publish boilerplate for a Clumsy resource
 *
 * @author Tomas Buteler <tbuteler@gmail.com>
 */
class ResourceCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'clumsy:resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes boilerplate for a Clumsy resource: migrations, seeds, controllers, model, panels and views';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $resource = $this->getResourceSlug();
        $resourceNamePluralInSnakeCase = $this->getResourceNamePluralInSnakeCase();

        $generates = [
            'model',
            'seed',
            'controller',
            'views folder',
            'table panel',
        ];

        foreach ($generates as $key) {
            $this->generate($key);
        }

        $this->call('make:migration', ['name' => str_replace('-', '_', "create_{$resourceNamePluralInSnakeCase}_table")]);

        $controller = array_get($this->generators, 'controller');
        $controller = $controller ? $controller->targetName() : null;
        $controllerFeedback = $controller ? "\n- Add this to routes.php:\nRoute::resource('{$resource}', '{$controller}');" : null;

        $seed = array_get($this->generators, 'seed');
        $seed = $seed ? $seed->targetName() : null;
        $seedFeedback = $seed ? "\n- Add this to DatabaseSeeder.php:\n\$this->call({$seed}::class);" : null;
        $this->info("All done!{$controllerFeedback}{$seedFeedback}");
    }

    protected function getArguments()
    {
        return [
            ['resource_name', InputArgument::REQUIRED, 'The name of the desired Clumsy resource']
        ];
    }
}
