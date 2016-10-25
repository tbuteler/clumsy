<?php

namespace Clumsy\CMS\Auth;

use Illuminate\Auth\AuthManager as LaravelAuthManager;

class AuthManager extends LaravelAuthManager
{
    /**
     * Get the default authentication driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return 'clumsy';
    }

    /**
     * Set the default authentication driver name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name) {}
}
