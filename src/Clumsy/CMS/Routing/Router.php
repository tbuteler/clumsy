<?php namespace Clumsy\CMS\Routing;

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Routing\Router as BaseRouter;

class Router extends BaseRouter {

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
        $base = $this->getResourceWildcard(last(explode('.', $name)));

        $this->addResourceIndexOfType($name, $base, $controller, $options);

        parent::resource($name, $controller, $options);
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
}