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
	public function register() {}

	/**
	 * Boot the service provider.
	 *
	 * @return void
	 */
	public function boot()
	{
        $this->package('clumsy/cms', 'clumsy/cms');
        $this->app['config']->package('clumsy/cms', $this->guessPackagePath() . '/config');

        $admin_assets = include($this->guessPackagePath() . '/assets/assets.php');
		Asset::batchRegister($admin_assets);

		require $this->guessPackagePath().'/filters.php';
		require $this->guessPackagePath().'/routes.php';
		require $this->guessPackagePath().'/errors.php';

		require $this->guessPackagePath().'/helpers.php';
		require $this->guessPackagePath().'/macros/form.php';
		require $this->guessPackagePath().'/macros/html.php';
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
