<?php

namespace Clumsy\CMS\Console;

/**
 * Publish boilerplate for a Clumsy resource
 *
 * @author Tomas Buteler <tbuteler@gmail.com>
 */
class ResourceCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clumsy:resource
                            {resource : The name of the resource to be created}
                            {--pivot=* : Which resource(s), if any, have a many-to-many relation with the resource being created. If the related resources do not exist, they will be created}
                            {--only= : Generate only a comma-separated list of objects for the resource}
                            {--except= : Generate all except a comma-separated list of objects for the resource}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish boilerplate for a Clumsy resource: migrations, seeds, controllers, model, panels and views';

    protected $pivotUseDeclarations;

    protected $pivotTraits;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->parsePivots();
        $this->pivotUseDeclarations = collect();
        $this->pivotTraits = collect();

        foreach ($this->pivotResources as $pivot) {
            $this->registerPivotResource($pivot);
        }

        $this->generateResource($this->getResourceSlug(), $this->pivotResources);

        foreach ($this->pivotResources as $pivot) {
            $this->createPivotResource($pivot);
        }

        if (count(array_get($this->generators, 'controller'))) {
            $code = '';
            foreach (array_get($this->generators, 'controller') as $controllerResource => $controller) {
                $controller = $controller->targetName();
                $code .= "Route::resource('{$controllerResource}', '{$controller}');\n";
            }
            $this->info("\n- Add this to routes.php:\n");
            $this->line($code);
        }

        if (count(array_get($this->generators, 'seed'))) {
            $code = '';
            foreach (array_get($this->generators, 'seed') as $seed) {
                $seed = $seed->targetName();
                $code .= "\$this->call({$seed}::class);\n";
            }
            $this->info("\n- Add this to DatabaseSeeder.php:\n");
            $this->line($code);
        }
    }

    protected function registerPivotResource($resourceName)
    {
        $generator = $this->newGenerator('pivot-trait')->setData('objectName', studly_case($resourceName));
        $trait = $generator->targetName();
        $traitNamespaced = $generator->getNamespace().'\\'.$trait;
        $this->pivotUseDeclarations->push($traitNamespaced);
        $this->pivotTraits->push($trait);
    }

    protected function createPivotResource($resourceName)
    {
        $this->call('clumsy:pivot', ['resource' => $resourceName, '--pivot' => [$this->getResourceSlug()]]);
    }

    protected function pivotUseDeclarations()
    {
        if ($this->pivotUseDeclarations->isEmpty()) {
            return null;
        }

        $declarations = '';
        foreach ($this->pivotUseDeclarations as $declaration) {
            $declarations .= "use $declaration;\n";
        }
        return $declarations;
    }
    protected function pivotTraits()
    {
        if ($this->pivotTraits->isEmpty()) {
            return null;
        }

        return ', '.$this->pivotTraits->implode(', ');
    }
}
