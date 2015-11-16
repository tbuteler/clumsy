<?php

namespace Clumsy\CMS;

use Closure;
use InvalidArgumentException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\AliasLoader;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Clumsy\CMS\Auth\Overseer;
use Clumsy\Assets\Facade as Asset;

class Clumsy
{
    protected $app;
    protected $auth;
    protected $view;
    protected $adminPrefix;

    public function __construct(Application $app, Overseer $auth)
    {
        $this->app = $app;
        $this->session = $this->app['session'];

        $adminLocale = $this->app['config']->get('clumsy.cms.admin-locale');
        $this->app['config']->set('app.locale', $adminLocale);
        $this->app->setLocale($adminLocale);

        $this->auth = $auth;
        $this->app->instance('clumsy.auth', $auth);

        $this->app['clumsy.view-resolver'] = $this->app->make('Clumsy\CMS\Support\ViewResolver');
        $this->view = $this->app['clumsy.view-resolver'];

        $this->app->instance('clumsy', $this);

        AliasLoader::getInstance()->alias('Form', 'Collective\Html\FormFacade');
        AliasLoader::getInstance()->alias('HTML', 'Collective\Html\HtmlFacade');
        require __DIR__.'/Support/macros/admin/html.php';
        require __DIR__.'/Support/macros/admin/form.php';

        $admin_assets = include(__DIR__.'/assets/assets.php');
        Asset::batchRegister($admin_assets);

        $this->adminPrefix = null;
        if (!$this->app->runningInConsole()) {
            $this->adminPrefix = ltrim(str_replace('/', '.', $this->app['request']->route()->getPrefix()), '.');
        }

        $this->app['clumsy.admin'] = true;
    }

    public function handle($request, Closure $next, $methods = null)
    {
        if (!$methods) {
            $methods = 'auth+assets+user';
        } elseif ($methods === 'init') {
            return $next($request);
        }

        $methods = explode('+', $methods);

        foreach ($methods as $method) {
            if (method_exists($this, $method)) {
                $response = $this->{$method}($request, $next);
                if ($response instanceof SymfonyResponse) {
                    return $response;
                }
            }
        }

        return $next($request);
    }

    public function auth($request, Closure $next)
    {
        if (!$this->auth->check()) {
            return redirect()->guest(route('clumsy.login'));
        }
    }

    public function assets($request, Closure $next)
    {
        view()->share([
            'adminPrefix'   => $this->prefix(),
            'navbarWrapper' => $this->view->resolve('navbar-wrapper'),
            'navbarHome'    => $this->view->resolve('navbar-home-link'),
            'navbar'        => $this->view->resolve('navbar'),
            'navbarButtons' => $this->view->resolve('navbar-buttons'),
            'view'          => $this->view,
            'alert'         => $this->session->get('alert', false),
            'bodyClass'     => str_replace('.', '-', $request->route()->getName()),
        ]);

        Asset::enqueue('admin.css', 10);
        Asset::enqueue('admin.js', 10);
        Asset::json('admin', [
            'prefix' => $this->prefix(),
            'urls' => [
                'base'   => url($this->prefix()),
            ],
            'strings' => [
                'filter_no_results'   => trans('clumsy::fields.filter-no-results'),
                'delete_confirm'      => trans('clumsy::alerts.delete_confirm'),
                'delete_confirm_user' => trans('clumsy::alerts.user.delete_confirm'),
            ],
        ], true);
    }

    public function user($request, Closure $next)
    {
        view()->share('user', $this->auth->user());
    }

    public function prefix()
    {
        return $this->adminPrefix;
    }

    public function panel($identifier)
    {
        if (class_exists($identifier)) {
            return $this->app->make($identifier);
        }

        $fallback = false;
        if (str_contains($identifier, '.')) {
            $sections = array_map('studly_case', explode('.', $identifier));
            $panel = array_pop($sections);
            $namespace = $this->app['config']->get('clumsy.cms.panel-namespace');
            $namespace .= '\\'.implode('\\', $sections);
            $class = "{$namespace}\\{$panel}";
            if (!class_exists($class)) {
                $fallback = true;
            }
        }

        if (!str_contains($identifier, '.') || $fallback) {
            if ($fallback) {
                $identifier = last(explode('.', $identifier));
            }
            $namespace = 'Clumsy\\CMS\\Panels';
            $panel = studly_case($identifier);
            $class = "{$namespace}\\{$panel}";
        }

        if (!class_exists($class)) {
            throw new InvalidArgumentException("Panel [{$class}] not found.");
        }

        return $this->app->make($class);
    }
}
