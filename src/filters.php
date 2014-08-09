<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Cartalyst\Sentry\Facades\Laravel\Sentry;
use Clumsy\Assets\Facade as Asset;

/*
|--------------------------------------------------------------------------
| Admin Filters
|--------------------------------------------------------------------------
|
*/

Route::filter('admin_assets', function()
{
    Asset::enqueue('admin.css');
    Asset::enqueue('admin.js');
    Asset::json('admin', array(
        'base_url'            => URL::to(Config::get('cms::admin_prefix')),
        'delete_confirm'      => trans('clumsy/cms::alerts.delete_confirm'),
        'delete_confirm_user' => trans('clumsy/cms::alerts.user.delete_confirm'),
    ));

    View::share('admin_prefix', Config::get('cms::admin_prefix'));

    $navbar = false;

    if (View::exists('admin.navbar'))
    {
        $navbar = 'admin.navbar';
    }
    elseif (View::exists('admin.templates.navbar'))
    {
        $navbar = 'admin.templates.navbar';
    }

    if ($navbar)
    {
        View::share('navbar', View::make($navbar)->render());
    }
});

Route::filter('admin_auth', function()
{
    if (!Sentry::check())
    {
        return Redirect::guest(Config::get('cms::admin_prefix').'/login');
    }
});