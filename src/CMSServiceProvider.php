<?php

namespace Clumsy\CMS;

use Clumsy\Assets\AssetsServiceProvider;
use Clumsy\CMS\Clumsy;
use Clumsy\CMS\Contracts\ShortcodeInterface;
use Clumsy\CMS\Facades\Overseer;
use Clumsy\CMS\Library\Shortcode;
use Clumsy\CMS\Routing\ResourceRegistrar;
use Clumsy\Eminem\EminemServiceProvider;
use Clumsy\Utils\UtilsServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class CMSServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(AssetsServiceProvider::class);
        $this->app->register(UtilsServiceProvider::class);
        $this->app->register(EminemServiceProvider::class);

        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'clumsy.cms');

        $loader = AliasLoader::getInstance();
        $loader->alias('Overseer', Overseer::class);

        $this->app['router']->aliasMiddleware('clumsy', Clumsy::class);

        // Override the resource registrar so we can add more methods to resource controllers
        $this->app->bind('Illuminate\Routing\ResourceRegistrar', function ($app) {
            return new ResourceRegistrar($app['router']);
        });

        $this->app->bind(ShortcodeInterface::class, Shortcode::class);

        $this->app['clumsy.admin'] = false;

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/lang', 'clumsy');

        $this->registerAuthRoutes();
        $this->registerPublishers();

        if ($this->app->runningInConsole()) {
            $this->app->make(Clumsy::class);
        }

        $this->loadViewsFrom(__DIR__.'/views', 'clumsy');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'clumsy',
            'clumsy.auth',
            'clumsy.admin',
            'clumsy.password',
            'clumsy.password.tokens',
        ];
    }

    protected function registerCommands()
    {
        $this->app->singleton('command.clumsy.publish', function ($app) {
            return new Console\PublishCommand();
        });

        $this->app->singleton('command.clumsy.resource', function ($app) {
            return new Console\ResourceCommand();
        });

        $this->app->singleton('command.clumsy.pivot', function ($app) {
            return new Console\PivotCommand();
        });

        $this->app->singleton('command.clumsy.user', function ($app) {
            return new Console\RegisterUserCommand();
        });

        $this->commands([
            'command.clumsy.publish',
            'command.clumsy.resource',
            'command.clumsy.pivot',
            'command.clumsy.user',
        ]);
    }

    protected function registerPublishers()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('clumsy/cms.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/lang' => base_path('resources/lang/vendor/clumsy'),
        ], 'translations');

        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/vendor/clumsy'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/clumsy/cms'),
        ], 'public');
    }

    protected function registerAuthRoutes()
    {
        $this->app['router']->group(
            [
                'namespace'  => 'Clumsy\CMS\Controllers',
                'prefix'     => $this->app['config']->get('clumsy.cms.authentication-prefix'),
                'middleware' => array_merge(
                    ['clumsy:init'],
                    (array)$this->app['config']->get('clumsy.cms.authentication-middleware')
                ),
            ],
            function () {

                $this->app['router']->get('is-logged-in', [
                    'as'         => 'clumsy.is-logged-in',
                    'uses'       => 'AuthController@isLoggedIn',
                ]);

                $this->app['router']->get('login', [
                    'as'         => 'clumsy.login',
                    'middleware' => 'clumsy:assets',
                    'uses'       => 'AuthController@getLogin',
                ]);

                $this->app['router']->post('login', [
                    'uses' => 'AuthController@postLogin',
                ]);

                $this->app['router']->get('reset', [
                    'as'         => 'clumsy.reset-password',
                    'middleware' => 'clumsy:assets',
                    'uses'       => 'AuthController@reset',
                ]);

                $this->app['router']->post('reset', [
                    'uses' => 'AuthController@postReset',
                ]);

                $this->app['router']->get('do-reset/{token}', [
                    'as'         => 'clumsy.do-reset-password',
                    'middleware' => 'clumsy:assets',
                    'uses'       => 'AuthController@doReset',
                ]);

                $this->app['router']->post('do-reset/{token}', [
                    'uses' => 'AuthController@postDoReset',
                ]);

                $this->app['router']->post('logout', [
                    'as'   => 'clumsy.logout',
                    'uses' => 'AuthController@getLogout',
                ]);

                $this->app['router']->group(
                    ['middleware' => 'clumsy'],
                    function () {

                        /*
                        |--------------------------------------------------------------------------
                        | Default admin home -- to be overridden by local apps
                        |--------------------------------------------------------------------------
                        |
                        */

                        $this->app['router']->get('/', [
                            'as' => 'admin.home',
                            function () {
                                return view('clumsy::index');
                            }
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | User resource
                        |--------------------------------------------------------------------------
                        |
                        */

                        $this->app['router']->resource('user', 'UsersController');
                    }
                );
            }
        );
    }
}
