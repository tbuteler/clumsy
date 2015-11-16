<?php

namespace Clumsy\CMS;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Clumsy\CMS\Routing\ResourceRegistrar;
use Clumsy\CMS\Facades\Overseer;

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
        $this->app->register('Clumsy\Assets\AssetsServiceProvider');
        $this->app->register('Clumsy\Utils\UtilsServiceProvider');
        $this->app->register('Clumsy\Eminem\EminemServiceProvider');

        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'clumsy.cms');

        $loader = AliasLoader::getInstance();
        $loader->alias('Overseer', Overseer::class);

        $this->app['router']->middleware('clumsy', 'Clumsy\CMS\Clumsy');

        // Override the resource registrar so we can add more methods to resource controllers
        $this->app->bind('Illuminate\Routing\ResourceRegistrar', function ($app) {
            return new ResourceRegistrar($app['router']);
        });

        $this->app->bind('\Clumsy\CMS\Contracts\ShortcodeInterface', '\Clumsy\CMS\Library\Shortcode');

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
        $this->loadTranslationsFrom(__DIR__.'/Support/lang', 'clumsy');

        $this->registerAuthRoutes();
        $this->registerPublishers();

        if ($this->app->runningInConsole()) {
            $this->app->make('Clumsy\CMS\Clumsy');
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
        $this->app['command.clumsy.publish'] = $this->app->share(function ($app) {
            return new Console\PublishCommand();
        });

        $this->app['command.clumsy.resource'] = $this->app->share(function ($app) {
            return new Console\ResourceCommand();
        });

        $this->commands([
            'command.clumsy.publish',
            'command.clumsy.resource',
        ]);
    }

    protected function registerPublishers()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('clumsy/cms.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/Support/lang' => base_path('resources/lang/vendor/clumsy'),
        ], 'translations');

        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/vendor/clumsy'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations')
        ], 'migrations');

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
                'middleware' => 'clumsy:init',
            ],
            function () {

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

                $this->app['router']->get('logout', [
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
