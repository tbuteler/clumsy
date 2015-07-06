<?php namespace Clumsy\CMS;

use Illuminate\Support\ServiceProvider;
use Clumsy\Assets\Facade as Asset;

class CMSServiceProvider extends ServiceProvider {

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

        // Override the default router so we can add more methods to resource controllers
        $this->app['router'] = $this->app->make('Clumsy\CMS\Routing\Router');

        $this->app['command.clumsy.publish'] = $this->app->share(function($app)
            {
                // Make sure the asset publisher is registered.
                $app->register('Illuminate\Foundation\Providers\PublisherServiceProvider');
                return new Console\PublishCommand($app['asset.publisher']);
            });

        $this->app['command.clumsy.resource'] = $this->app->share(function($app)
            {
                return new Console\ResourceCommand();
            });

        $this->commands(array('command.clumsy.publish', 'command.clumsy.resource'));

        $this->app->bind('\Clumsy\CMS\Contracts\ShortcodeInterface', '\Clumsy\CMS\Library\Shortcode');
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $path = __DIR__.'/../..';

        $this->package('clumsy/cms', 'clumsy', $path);

        $this->registerAuthRoutes();
        $this->registerBackEndRoutes();

        $admin_assets = include($path.'/assets/assets.php');
        Asset::batchRegister($admin_assets);

        require $path.'/filters.php';

        if ($this->app->runningInConsole())
        {
            $this->app->make('Clumsy\CMS\Clumsy');
        }

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'clumsy.shortcode',
        );
    }

    protected function registerAuthRoutes()
    {
        $this->app['router']->group(
            array(
                'namespace' => 'Clumsy\CMS\Controllers',
                'prefix'    => $this->app['config']->get('clumsy::authentication-prefix'),
                'before'    => 'clumsy:init',
            ),
            function()
            {
                $this->app['router']->get('login', array(
                    'as'       => 'clumsy.login',
                    'before'   => 'clumsy:assets',
                    'uses'     => 'AuthController@login',
                ));

                $this->app['router']->post('login', array(
                    'before'   => 'csrf',
                    'uses'     => 'AuthController@postLogin',
                ));

                $this->app['router']->get('reset', array(
                    'as'       => 'clumsy.reset-password',
                    'before'   => 'clumsy:assets',
                    'uses'     => 'AuthController@reset',
                ));

                $this->app['router']->post('reset', array(
                    'before'   => 'csrf',
                    'uses'     => 'AuthController@postReset',
                ));

                $this->app['router']->get('do-reset/{user_id}/{code}', array(
                    'as'       => 'clumsy.do-reset-password',
                    'before'   => 'clumsy:assets',
                    'uses'     => 'AuthController@doReset',
                ));

                $this->app['router']->post('do-reset/{user_id}/{code}', array(
                    'before'   => 'csrf',
                    'uses'     => 'AuthController@postDoReset',
                ));

                $this->app['router']->get('logout', array(
                    'as'       => 'clumsy.logout',
                    'uses'     => 'AuthController@logout',
                ));

                /*
                |--------------------------------------------------------------------------
                | User resource
                |--------------------------------------------------------------------------
                |
                */

                $this->app['router']->group(array(
                        'before' => 'clumsy',
                    ),
                    function()
                    {
                        $this->app['router']->resource('user', 'UsersController');
                    }
                );
            }
        );
    }

    protected function registerBackEndRoutes()
    {
        $this->app['router']->group(
            array(
                'namespace' => 'Clumsy\CMS\Controllers',
                'before' => 'clumsy:auth+user',
            ),
            function()
            {
                /*
                |--------------------------------------------------------------------------
                | Column sorting
                |--------------------------------------------------------------------------
                |
                */

                $this->app['router']->get('_reorder/{resource}', array(
                    'as'   => 'clumsy.reorder',
                    'uses' => 'BackEndController@reorder',
                ));

                /*
                |--------------------------------------------------------------------------
                | Index dynamic filters
                |--------------------------------------------------------------------------
                |
                */

                $this->app['router']->post('_filter/{resource}', array(
                    'as'   => 'clumsy.filter',
                    'uses' => 'BackEndController@filter',
                ));

                /*
                |--------------------------------------------------------------------------
                | AJAX entry updating (active booleans)
                |--------------------------------------------------------------------------
                |
                */

                $this->app['router']->post('_update', array(
                    'as'     => 'clumsy.update',
                    'before' => 'csrf',
                    'uses'   => 'BackEndController@update',
                ));

                /*
                |--------------------------------------------------------------------------
                | Active reorder
                |--------------------------------------------------------------------------
                |
                */

                $this->app['router']->post('_reorder/{resource}',array(
                    'as'   => 'clumsy.save-active-reorder',
                    'uses' => 'BackEndController@saveOrder',
                ));
            }
        );
    }
}
