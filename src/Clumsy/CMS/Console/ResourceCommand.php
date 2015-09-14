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
    protected $description = 'Publish boilerplate for a Clumsy resource: migrations, seeds, controllers, model and views';

    protected $generators = array();

    protected $template_data = array();

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $resource = $this->getResourceName();
        $resource_plural = $this->getPluralResourceName();

        $generates = array(
            'Model',
            'Seed',
            'Controller',
            'ViewsFolder',
        );

        foreach ($generates as $generate) {
            try {
                $template_data = $this->getTemplateData();

                $generator = App::make($this->getGeneratorClass($generate));
                $generator->make($template_data);

                $this->generators[$generate] = $generator;
                $this->template_data[$generate] = $template_data;

                $this->info("Created {$resource} {$generate}");
            } catch (FileAlreadyExists $e) {
                $this->error("The file for a {$resource} {$generate} already exists! I don't want to overwrite it. Aborting...");

                return false;
            }
        }

        $this->call('migrate:make', array('name' => "create_{$resource_plural}_table"));

        $controller = $this->generators['Controller']->targetName($this->template_data['Controller']);
        $this->info("All done! Here's your resourceful route: `Route::resource('{$resource}', '{$controller}');`");
    }

    protected function getArguments()
    {
        return array(
            array('resource_name', InputArgument::REQUIRED, 'The name of the desired Clumsy resource')
        );
    }

    protected function getGeneratorClass($model)
    {
        return '\Clumsy\CMS\Generators\\'.$model;
    }


    /**
     * Fetch the template data.
     *
     * @return array
     */
    protected function getTemplateData()
    {
        return array(
            'name'               => $this->getResourceName(),
            'plural'             => $this->getPluralResourceName(),
            'object_name'        => $this->getObjectName(),
            'object_name_plural' => $this->getPluralObjectName(),
        );
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
