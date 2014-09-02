<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;
use Cartalyst\Sentry\Facades\Laravel\Sentry;
use Clumsy\Assets\Facade as Asset;

/*
|--------------------------------------------------------------------------
| Admin Filters
|--------------------------------------------------------------------------
|
*/

Route::filter('admin_auth', function()
{
    if (!Sentry::check())
    {        
        return Redirect::guest(Config::get('clumsy::admin_prefix').'/login');
    }
});

Route::filter('admin_assets', function()
{
    Asset::enqueue('admin.css');
    Asset::enqueue('admin.js');
    Asset::json('admin', array(
        'base_url'            => URL::to(Config::get('clumsy::admin_prefix')),
        'delete_confirm'      => trans('clumsy::alerts.delete_confirm'),
        'delete_confirm_user' => trans('clumsy::alerts.user.delete_confirm'),
    ));

    View::share('admin_prefix', Config::get('clumsy::admin_prefix'));

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

    $alert = $alert_status = false;

    if (Session::has('alert'))
    {
        $alert = Session::get('alert');
        $alert_status = Session::has('alert_status') ? Session::get('alert_status') : 'warning';
    }

    View::share('alert', $alert);
    View::share('alert_status', $alert_status);
});

Route::filter('admin_user', function()
{
    $user = Sentry::getUser();

    $username = array_filter(array(
        $user->first_name,
        $user->last_name,
    ));

    if (!count($username))
    {
        $username = (array)$user->email;
    }

    $usergroup = str_singular($user->getGroups()->first()->name);

    if (Lang::has('clumsy::fields.roles.'.Str::lower($usergroup)))
    {
        $usergroup = trans('clumsy::fields.roles.'.Str::lower($usergroup));
    }

    View::share('user', $user);
    View::share('username', implode(' ', $username));
    View::share('usergroup', $usergroup);
});