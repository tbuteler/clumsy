<?php

namespace Clumsy\CMS\Auth;

use Illuminate\Auth\AuthManager as BaseAuthManager;
use Illuminate\Auth\EloquentUserProvider;

class AuthManager extends BaseAuthManager
{
    protected $model;

    public function __construct($app)
    {
        $this->app = $app;
        $this->model = $app['config']['clumsy.cms.authentication-model'];
    }

    public function createEloquentDriver()
    {
        $provider = $this->createEloquentProvider();
        return new Guard($provider, $this->app['session.store']);
    }

    protected function createEloquentProvider()
    {
        return new EloquentUserProvider($this->app['hash'], $this->model);
    }

    public function getDefaultDriver()
    {
        return 'eloquent';
    }

    public function getEloquentModel()
    {
        return $this->model;
    }
}
