<?php

namespace Clumsy\CMS\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Clumsy\CMS\Generators\Filesystem\FileAlreadyExists;

/**
 * Base generator command
 *
 * @author Tomas Buteler <tbuteler@gmail.com>
 */
abstract class GeneratorCommand extends Command
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

    protected function generate($key)
    {
        $resource = $this->getResourceName();

        try {

            $generatorClass = $this->getGeneratorClass($key);
            $generator = new $generatorClass;
            $generator->data($this->getTemplateData());
            $generator->make();

            $this->generators[$key] = $generator;

            $this->info("Created [{$resource}] {$key}");

        } catch (FileAlreadyExists $e) {

            $this->error("The file for a [{$resource}] {$key} already exists! I don't want to overwrite it. Aborting...");

            return false;
        }
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
            'nameInSnakeCase'    => $this->getResourceNameInSnakeCase(),
            'pluralInSnakeCase'  => $this->getResourceNamePluralInSnakeCase(),
            'slug'               => $this->getResourceSlug(),
            'object_name'        => $this->getObjectName(),
            'object_name_plural' => $this->getPluralObjectName(),
        ];
    }

    protected function getResourceName()
    {
        return camel_case($this->argument('resource_name'));
    }

    protected function getPluralResourceName()
    {
        return str_plural($this->getResourceName());
    }

    protected function getResourceNameInSnakeCase()
    {
        return snake_case(camel_case($this->argument('resource_name')));
    }

    protected function getResourceNamePluralInSnakeCase()
    {
        return str_plural($this->getResourceNameInSnakeCase());
    }

    protected function getResourceSlug()
    {
        return str_slug($this->getResourceNameInSnakeCase());
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
