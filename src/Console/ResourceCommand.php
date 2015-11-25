<?php
namespace Clumsy\CMS\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Clumsy\CMS\Generators\Controller;
use Clumsy\CMS\Generators\Model;
use Clumsy\CMS\Generators\Seed;
use Clumsy\CMS\Generators\Filesystem\FileAlreadyExists;

/**
 * Publish boilerplate for a Clumsy resource
 *
 * @author Tomas Buteler <tbuteler@gmail.com>
 */
class ResourceCommand extends Command
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
    protected $description = 'Publish boilerplate for a Clumsy resource: migrations, seeds, controllers, model, panels and views';

    protected $generators = [];

    protected $templateData = [];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $resource = $this->getResourceName();
        $resource_plural = $this->getPluralResourceName();

        $generates = [
            'model',
            'seed',
            'controller',
            'views folder',
            'table panel',
        ];

        foreach ($generates as $generate) {

            try {

                $generatorClass = $this->getGeneratorClass($generate);
                $generator = new $generatorClass;
                $generator->data($this->getTemplateData());
                $generator->make();

                $this->generators[$generate] = $generator;

                $this->info("Created [{$resource}] {$generate}");

            } catch (FileAlreadyExists $e) {

                $this->error("The file for a [{$resource}] {$generate} already exists! I don't want to overwrite it. Aborting...");

                return false;
            }
        }

        $this->call('make:migration', ['name' => "create_{$resource_plural}_table"]);

        $controller = $this->generators['controller']->targetName();
        $seed = $this->generators['seed']->targetName();
        $this->info("All done!\n- Add this to routes.php: [Route::resource('{$resource}', '{$controller}');]\n- Add this to DatabaseSeeder.php: [\$this->call({$seed}::class);]");
    }

    protected function getArguments()
    {
        return [
            ['resource_name', InputArgument::REQUIRED, 'The name of the desired Clumsy resource']
        ];
    }

    protected function getGeneratorClass($model)
    {
        return '\\Clumsy\\CMS\\Generators\\'.studly_case($model);
    }

    /**
     * Fetch the template data.
     *
     * @return array
     */
    protected function getTemplateData()
    {
        return [
            'name'               => $this->getResourceName(),
            'plural'             => $this->getPluralResourceName(),
            'object_name'        => $this->getObjectName(),
            'object_name_plural' => $this->getPluralObjectName(),
        ];
    }

    protected function getResourceName()
    {
        return snake_case($this->argument('resource_name'));
    }

    protected function getPluralResourceName()
    {
        return str_plural($this->getResourceName());
    }

    protected function getObjectName()
    {
        return studly_case($this->getResourceName());
    }

    protected function getPluralObjectName()
    {
        return studly_case($this->getPluralResourceName());
    }
}
