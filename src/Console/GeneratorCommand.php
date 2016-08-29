<?php

namespace Clumsy\CMS\Console;

use Illuminate\Console\Command;
use Clumsy\CMS\Generators\Filesystem\FileAlreadyExists;

/**
 * Base generator command
 *
 * @author Tomas Buteler <tbuteler@gmail.com>
 */
abstract class GeneratorCommand extends Command
{
    protected $generators = [];

    protected $templateData = [];

    protected $pivotResources;

    protected function generate($resource, $key, $data = null)
    {
        try {
            $generator = $this->newGenerator($key);
            $generator->data($data ?: $this->generateTemplateData($resource));
            $generator->make();

            array_set($this->generators, "{$key}.{$resource}", $generator);

            $generator = $generator->readableName();
            $this->info("Created [{$resource}] {$generator}");

        } catch (FileAlreadyExists $e) {
            $generator = $generator->readableName();
            $this->error("The file for a [{$resource}] {$generator} already exists! Aborting...");

            return false;
        }
    }

    protected function getGeneratorClass($model)
    {
        return '\\Clumsy\\CMS\\Generators\\'.studly_case($model);
    }

    protected function newGenerator($key)
    {
        $generatorClass = $this->getGeneratorClass($key);
        return app()->make($generatorClass);
    }

    /**
     * Fetch the template data.
     *
     * @return array
     */
    protected function generateTemplateData($resource = null)
    {
        return [
            'name'              => $this->getResourceName($resource),
            'plural'            => $this->getPluralResourceName($resource),
            'nameInSnakeCase'   => $this->getResourceNameInSnakeCase($resource),
            'pluralInSnakeCase' => $this->getResourceNamePluralInSnakeCase($resource),
            'slug'              => $this->getResourceSlug($resource),
            'objectName'        => $this->getObjectName($resource),
            'objectNamePlural'  => $this->getPluralObjectName($resource),
        ];
    }

    protected function getResource()
    {
        return $this->argument('resource');
    }

    protected function getResourceName($resource = null)
    {
        return camel_case($resource ?: $this->getResource());
    }

    protected function getPluralResourceName($resource = null)
    {
        return str_plural($this->getResourceName($resource));
    }

    protected function getResourceNameInSnakeCase($resource = null)
    {
        return snake_case(camel_case($resource ?: $this->getResource()));
    }

    protected function getResourceNamePluralInSnakeCase($resource = null)
    {
        return str_plural($this->getResourceNameInSnakeCase($resource));
    }

    protected function getResourceSlug($resource = null)
    {
        return str_slug($this->getResourceNameInSnakeCase($resource));
    }

    protected function getObjectName($resource = null)
    {
        return studly_case($this->getResourceName($resource));
    }

    protected function getPluralObjectName($resource = null)
    {
        return studly_case($this->getPluralResourceName($resource));
    }

    protected function parseOnly()
    {
        return array_filter(explode(',', $this->option('only')));
    }

    protected function parseExcept()
    {
        return array_filter(explode(',', $this->option('except')));
    }

    protected function parsePivots()
    {
        $this->pivotResources = collect();

        foreach ($this->option('pivot') as $pivots) {
            foreach (explode(',', $pivots) as $pivot) {
                $this->pivotResources->push($this->getResourceSlug($pivot));
            }
        }
    }

    protected function resourceExists($resourceName)
    {
        return $this->newGenerator('model')->setData('objectName', studly_case($resourceName))->exists();
    }

    protected function generateResource($resource, $pivots = [])
    {
        $this->line("Generating [{$resource}] resource...");

        $generates = array_flip([
            'model',
            'seed',
            'controller',
            'views folder',
            'table panel',
            'migration-create',
        ]);

        if (count($this->parseOnly())) {
            $generates = array_only($generates, $this->parseOnly());
        }

        if (count($this->parseExcept())) {
            $generates = array_except($generates, $this->parseExcept());
        }

        foreach (array_keys($generates) as $key) {
            $data = array_merge($this->generateTemplateData($resource), [
                'pivotUseDeclarations' => count($pivots) ? $this->pivotUseDeclarations() : null,
                'pivotTraits'          => count($pivots) ? $this->pivotTraits() : null,
            ]);
            $this->generate($resource, $key, $data);
        }

        $this->info("Resource [{$resource}] generated!");
    }
}
