<?php
namespace Clumsy\CMS;

use Illuminate\Foundation\Application;
use Cartalyst\Sentry\Facades\Laravel\Sentry;

class Overseer
{
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->app->register('Cartalyst\Sentry\SentryServiceProvider');

        $this->app['config']->set(
            'cartalyst/sentry::users.login_attribute',
            (string)$this->app['config']->get('clumsy::authentication-attribute')
        );

        $this->app['config']->set(
            'cartalyst/sentry::throttling.enabled',
            (bool)$this->app['config']->get('clumsy::authentication-throttling')
        );

        $model = $this->app['config']->get('clumsy::authentication-model');
        if ($model) {
            $this->app['config']->set('cartalyst/sentry::users.model', $model);
        }
    }

    protected function auth()
    {
        return $this->app['sentry'];
    }

    protected function gate()
    {
        return $this->app['sentry'];
    }

    public function guest()
    {
        return !$this->auth()->check();
    }

    public function attempt(array $credentials, $remember = false)
    {
        return $this->auth()->authenticate($credentials, $remember);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->auth(), $name), $arguments);
    }
}
