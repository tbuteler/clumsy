<?php

namespace Clumsy\CMS\Routing;

use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;

class ResourceRegistrar extends BaseResourceRegistrar
{
    protected $base;

    /**
     * The default actions for a resourceful controller.
     *
     * @var array
     */
    protected $resourceDefaults = [
        'index',
        'index-of-type',
        'create',
        'store',
        'edit',
        'update',
        'destroy',
        'sort',
        'filter',
        'reorder',
        'update-order',
    ];

    /**
     * Route a resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array   $options
     * @return void
     */
    public function register($name, $controller, array $options = [])
    {
        if (isset($options['external']) && $options['external']) {
            $this->externalResource($name, $controller, $options);

            return;
        }

        // If the resource name contains a slash, we will assume the developer wishes to
        // register these resource routes with a prefix so we will set that up out of
        // the box so they don't have to mess with it. Otherwise, we will continue.
        if (str_contains($name, '/')) {
            $this->prefixedResource($name, $controller, $options);

            return;
        }

        // We need to extract the base resource from the resource name. Nested resources
        // are supported in the framework, but we need to know what name to use for a
        // place-holder on the route wildcards, which should be the base resources.
        $this->base = $this->getResourceWildcard(last(explode('.', $name)));

        $defaults = $this->resourceDefaults;

        foreach ($this->getResourceMethods($defaults, $options) as $m) {
            $this->{'addResource'.studly_case($m)}($name, $this->base, $controller, $options);
        }
    }

    /**
     * Route an external resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array   $options
     * @return void
     */
    public function externalResource($name, $controller, array $options = [])
    {
        if (!isset($options['except'])) {
            $options['except'] = [];
        }

        $options['except'] = array_merge($options['except'], ['create', 'store', 'destroy']);
        unset($options['external']);

        $this->register($name, $controller, $options);

        $this->addResourceImport($name, $this->base, $controller, $options);
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

        return $this->router->get($this->getResourceUri($name).'/type/{type}', $action);
    }

    /**
     * Add the sort method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return Route
     */
    protected function addResourceSort($name, $base, $controller, $options)
    {
        $action = $this->getResourceAction($name, $controller, 'sort', $options);

        return $this->router->get($this->getResourceUri($name).'/sort', $action);
    }

    /**
     * Add the filter method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return Route
     */
    protected function addResourceFilter($name, $base, $controller, $options)
    {
        $action = $this->getResourceAction($name, $controller, 'filter', $options);

        return $this->router->post($this->getResourceUri($name).'/filter', $action);
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

        return $this->router->get($this->getResourceUri($name).'/reorder', $action);
    }

    /**
     * Add the update order method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return Route
     */
    protected function addResourceUpdateOrder($name, $base, $controller, $options)
    {
        $action = $this->getResourceAction($name, $controller, 'update-order', $options);
        $action['uses'] = str_replace('update-order', 'updateOrder', $action['uses']);

        return $this->router->post($this->getResourceUri($name).'/update-order', $action);
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

        return $this->router->get($this->getResourceUri($name).'/import', $action);
    }
}
