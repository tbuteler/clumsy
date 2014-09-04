<?php namespace Clumsy\CMS;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
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
		$this->app->register('Cartalyst\Sentry\SentryServiceProvider');
		$this->app->register('Clumsy\Locale\LocaleServiceProvider');
		$this->app->register('Clumsy\Assets\AssetsServiceProvider');
		$this->app->register('Clumsy\Utils\UtilsServiceProvider');
		$this->app->register('Clumsy\Eminem\EminemServiceProvider');
	}

	/**
	 * Boot the service provider.
	 *
	 * @return void
	 */
	public function boot()
	{
		View::addNamespace('clumsy', $this->guessPackagePath() . '/views');
		Lang::addNamespace('clumsy', $this->guessPackagePath() . '/lang');

        $this->package('clumsy/cms', 'clumsy');
        $this->app['config']->package('clumsy/cms', $this->guessPackagePath() . '/config');

        $admin_assets = include($this->guessPackagePath() . '/assets/assets.php');
		Asset::batchRegister($admin_assets);
		
		require $this->guessPackagePath().'/helpers.php';
		require $this->guessPackagePath().'/macros/form.php';
		require $this->guessPackagePath().'/filters.php';
		require $this->guessPackagePath().'/routes.php';
		require $this->guessPackagePath().'/errors.php';
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
