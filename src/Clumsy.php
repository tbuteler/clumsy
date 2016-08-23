<?php

namespace Clumsy\CMS;

use Closure;
use InvalidArgumentException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Clumsy\CMS\Auth\Overseer;

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

        $this->auth = $auth;
        $this->app->instance('clumsy.auth', $auth);

        $this->app['clumsy.view-resolver'] = $this->app->make('Clumsy\CMS\Support\ViewResolver');
        $this->view = $this->app['clumsy.view-resolver'];

        $this->app->instance('clumsy', $this);

        AliasLoader::getInstance()->alias('Form', 'Collective\Html\FormFacade');
        AliasLoader::getInstance()->alias('Field', 'Clumsy\Utils\Facades\Field');

        $adminAssets = include(__DIR__.'/assets/assets.php');
        $this->app['clumsy.assets']->batchRegister($adminAssets);

        $this->adminPrefix = null;
        if (!$this->app->runningInConsole()) {

            $this->adminPrefix = ltrim(str_replace('/', '.', $this->app['request']->route()->getPrefix()), '.');

            $adminLocale = $this->app['config']->get('clumsy.cms.admin-locale');
            $this->app['config']->set('app.locale', $adminLocale);
            $this->app->setLocale($adminLocale);

            $this->app['clumsy.admin'] = true;
        }

        $this->registerBladeDirectives();
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
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            }
            return redirect()->guest(route('clumsy.login'));
        }
    }

    public function assets($request)
    {
        view()->share([
            'adminPrefix'     => $this->prefix(),
            'userRoutePrefix' => 'user',
            'navbarWrapper'   => $this->view->resolve('navbar-wrapper'),
            'navbarHome'      => $this->view->resolve('navbar-home-link'),
            'navbar'          => $this->view->resolve('navbar'),
            'navbarButtons'   => $this->view->resolve('navbar-buttons'),
            'view'            => $this->view,
            'alert'           => $this->session->get('alert', false),
            'bodyClass'       => str_replace('.', '-', $request->route()->getName()),
        ]);

        $this->app['clumsy.assets']->load('admin.css', 10);
        $this->app['clumsy.assets']->load('admin.js', 10);
        $this->app['clumsy.assets']->json('admin', [
            'prefix' => $this->prefix(),
            'translations' => [
                'cancel' => trans('clumsy::buttons.cancel'),
                'alert'  => trans('clumsy::alerts.alert'),
                'remove' => trans('clumsy::buttons.remove'),
            ],
            'urls' => [
                'base' => url($this->prefix()),
            ],
        ]);
    }

    public function user()
    {
        view()->share('user', $this->auth->user());
    }

    public function prefix()
    {
        return $this->adminPrefix;
    }

    public function isAdmin()
    {
        return (bool) $this->app->offsetGet('clumsy.admin');
    }

    public function panel($identifier, $fallback = true)
    {
        if ($class = $this->panelClass($identifier)) {
            return $this->app->make($class);
        }

        // Before proceeding to generic Clumsy panel, check for app-specific inherited panels
        $inheritance = [
            'create' => 'edit',
        ];
        foreach ($inheritance as $from => $to) {
            if (ends_with(Str::lower($identifier), ".{$from}")) {
                $inherited = preg_replace("/\.{$from}$/i", ".{$to}", $identifier);
                if ($this->panelExists($inherited)) {
                    $inherited = $this->app->make($this->panelClass($inherited));
                    if ($inherited->isInheritable()) {
                        $inherited->action($from);
                        return $inherited;
                    }
                }
            }
        }

        if ($fallback) {
            $identifier = last(explode('.', $identifier));
            $namespace = 'Clumsy\\CMS\\Panels';
            $panel = studly_case($identifier);
            $class = "{$namespace}\\{$panel}";
            if (class_exists($class)) {
                return $this->app->make($class);
            }
        }

        throw new InvalidArgumentException("Panel [{$identifier}] not found.");
    }

    public function panelClass($identifier)
    {
        if (class_exists($identifier)) {
            return $identifier;
        }

        $sections = array_map('studly_case', explode('.', $identifier));
        $panel = array_pop($sections);
        $namespace = $this->app['config']->get('clumsy.cms.panel-namespace');
        $namespace .= '\\'.implode('\\', $sections);
        $class = "{$namespace}\\{$panel}";
        if (class_exists($class)) {
            return $class;
        }

        return false;
    }

    public function panelExists($identifier)
    {
        return (bool) $this->panelClass($identifier);
    }

    protected function registerBladeDirectives()
    {
        Blade::directive('mediaBox', function ($expression) {
            return "<?php echo \$panel->getItem()->mediaBox({$expression}); ?>";
        });

        Blade::directive('location', function ($expression) {
            return "<?php echo \$panel->location({$expression}); ?>";
        });

        Blade::directive('breadcrumb', function ($expression) {
            return "<?php echo isset(\$panel) ? \$panel->getBakery()->render({$expression}) : with(app()->make('Clumsy\CMS\Support\Bakery'))->render({$expression}); ?>";
        });

        Blade::directive('pivot', function ($expression) {
            return "<?php echo \$panel->pivotField({$expression}); ?>";
        });
    }
}
