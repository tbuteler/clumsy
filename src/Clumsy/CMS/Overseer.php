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

        $this->auth = $this->app['sentry'];
        $this->gate = $this->app['sentry'];
    }

    public function attempt(array $credentials, $remember = false)
    {
        return $this->auth->authenticate($credentials, $remember);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->auth, $name), $arguments);
    }
}
