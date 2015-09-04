<?php
namespace Clumsy\CMS\Routing;

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Routing\Router as BaseRouter;

class Router extends BaseRouter
{
    /**
     * The default actions for a resourceful controller.
     *
     * @var array
     */
    protected $externalResourceDefaults = array('import', 'index-of-type', 'index', 'show', 'edit', 'update', 'reorder');

    /**
     * The default actions for a resourceful controller.
     *
     * @var array
     */
    protected $resourceDefaults = array('index', 'create', 'store', 'edit', 'update', 'destroy', 'reorder');

    /**
     * Route a resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array   $options
     * @return void
     */
    public function resource($name, $controller, array $options = array())
    {
        parent::resource($name, $controller, $options);

        $base = $this->getResourceWildcard(last(explode('.', $name)));
        $this->addResourceIndexOfType($name, $base, $controller, $options);
    }

    /**
     * Route an external resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array   $options
     * @return void
     */
    public function externalResource($name, $controller, array $options = array())
    {
        if (str_contains($name, '/')) {
            $this->prefixedExternalResource($name, $controller, $options);

            return;
        }

        $base = $this->getResourceWildcard(last(explode('.', $name)));

        $defaults = $this->externalResourceDefaults;

        foreach ($this->getResourceMethods($defaults, $options) as $m) {
            $this->{'addResource'.studly_case($m)}($name, $base, $controller, $options);
        }
    }

    /**
     * Build a set of prefixed external resource routes.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array   $options
     * @return void
     */
    protected function prefixedResource($name, $controller, array $options)
    {
        list($name, $prefix) = $this->getResourcePrefix($name);

        $callback = function ($me) use ($name, $controller, $options) {

            $me->externalResource($name, $controller, $options);
        };

        return $this->group(compact('prefix'), $callback);
    }

    /**
     * Add the "index of type" method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return Route
     */
    protected function addResourceIndexOfType($name, $base, $controller, $options)
    {
        $action = $this->getResourceAction($name, $controller, 'index-of-type', $options);
        $action['uses'] = str_replace('index-of-type', 'indexOfType', $action['uses']);

        return $this->get($this->getResourceUri($name).'/type/{type}', $action);
    }

    /**
     * Add the reorder method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return Route
     */
    protected function addResourceReorder($name, $base, $controller, $options)
    {
        $action = $this->getResourceAction($name, $controller, 'reorder', $options);

        return $this->get($this->getResourceUri($name).'/reorder', $action);
    }

    /**
     * Add the import method for an external resource.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return Route
     */
    protected function addResourceImport($name, $base, $controller, $options)
    {
        $action = $this->getResourceAction($name, $controller, 'import', $options);

        return $this->get($this->getResourceUri($name).'/import', $action);
    }
}
