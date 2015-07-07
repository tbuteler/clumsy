<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Clumsy\CMS\Facades\Clumsy;

/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
|
*/

App::pushError(function(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception)
{
    if (Config::get('clumsy::silent'))
    {
        return Redirect::to('/admin');
    }
});

App::pushError(function(Illuminate\Session\TokenMismatchException $exception)
{
    if (Config::get('clumsy::silent') && isAdmin())
    {
        return Redirect::back()->with(array(
            'alert_status' => 'warning',
            'alert'        => trans('clumsy::alerts.token_mismatch'),
        ));
    }
});