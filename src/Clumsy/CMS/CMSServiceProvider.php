<?php
namespace Clumsy\CMS;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Clumsy\Assets\Facade as Asset;

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

        // Override the default router so we can add more methods to resource controllers
        $this->app['router'] = $this->app->make('Clumsy\CMS\Routing\Router');

        $this->app['command.clumsy.publish'] = $this->app->share(function ($app) {

                // Make sure the asset publisher is registered.
                $app->register('Illuminate\Foundation\Providers\PublisherServiceProvider');
                return new Console\PublishCommand($app['asset.publisher']);
        });

        $this->app['command.clumsy.resource'] = $this->app->share(function ($app) {

                return new Console\ResourceCommand();
        });

        $this->commands(array('command.clumsy.publish', 'command.clumsy.resource'));

        $this->app->bind('\Clumsy\CMS\Contracts\ShortcodeInterface', '\Clumsy\CMS\Library\Shortcode');

        $this->app['clumsy.admin'] = false;
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

        require $path.'/helpers.php';
        require $path.'/errors.php';
        require $path.'/filters.php';

        if ($this->app->runningInConsole()) {
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
            'clumsy.admin',
            'clumsy.shortcode',
        );
    }

    protected function registerAuthRoutes()
    {
        Route::group(
            array(
                'namespace' => 'Clumsy\CMS\Controllers',
                'prefix'    => $this->app['config']->get('clumsy::authentication-prefix'),
                'before'    => 'clumsy:init',
            ),
            function () {

                Route::get('login', array(
                    'as'       => 'clumsy.login',
                    'before'   => 'clumsy:assets',
                    'uses'     => 'AuthController@login',
                ));

                Route::post('login', array(
                    'before'   => 'csrf',
                    'uses'     => 'AuthController@postLogin',
                ));

                Route::get('reset', array(
                    'as'       => 'clumsy.reset-password',
                    'before'   => 'clumsy:assets',
                    'uses'     => 'AuthController@reset',
                ));

                Route::post('reset', array(
                    'before'   => 'csrf',
                    'uses'     => 'AuthController@postReset',
                ));

                Route::get('do-reset/{user_id}/{code}', array(
                    'as'       => 'clumsy.do-reset-password',
                    'before'   => 'clumsy:assets',
                    'uses'     => 'AuthController@doReset',
                ));

                Route::post('do-reset/{user_id}/{code}', array(
                    'before'   => 'csrf',
                    'uses'     => 'AuthController@postDoReset',
                ));

                Route::get('logout', array(
                    'as'       => 'clumsy.logout',
                    'uses'     => 'AuthController@logout',
                ));

                /*
                |--------------------------------------------------------------------------
                | User resource
                |--------------------------------------------------------------------------
                |
                */

                Route::group(
                    array(
                        'before' => 'clumsy',
                    ),
                    function () {

                        Route::resource('user', 'UsersController');
                    }
                );
            }
        );
    }

    protected function registerBackEndRoutes()
    {
        Route::group(
            array(
                'namespace' => 'Clumsy\CMS\Controllers',
                'before' => 'clumsy:auth+user',
            ),
            function () {

                /*
                |--------------------------------------------------------------------------
                | Column sorting
                |--------------------------------------------------------------------------
                |
                */

                Route::get('_reorder/{resource}', array(
                    'as'   => 'clumsy.reorder',
                    'uses' => 'BackEndController@reorder',
                ));

                /*
                |--------------------------------------------------------------------------
                | Index dynamic filters
                |--------------------------------------------------------------------------
                |
                */

                Route::post('_filter/{resource}', array(
                    'as'   => 'clumsy.filter',
                    'uses' => 'BackEndController@filter',
                ));

                /*
                |--------------------------------------------------------------------------
                | AJAX entry updating (active booleans)
                |--------------------------------------------------------------------------
                |
                */

                Route::post('_update', array(
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

                Route::post('_reorder/{resource}', array(
                    'as'   => 'clumsy.save-active-reorder',
                    'uses' => 'BackEndController@saveOrder',
                ));
            }
        );
    }
}
