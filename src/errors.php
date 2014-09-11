<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
|
*/

App::error(function(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception)
{
    Log::error('NotFoundHttpException Route: '.Request::url());
	Log::error($exception);

	if (Config::get('clumsy::silent'))
	{
    	return Redirect::to('/');
	}
});

App::error(function(Illuminate\Session\TokenMismatchException $exception)
{
	if (Config::get('clumsy::silent') && Route::getCurrentRoute()->getPrefix() === Config::get('clumsy::admin_prefix'))
	{
    	return Redirect::back()->with(array(
            'alert_status' => 'warning',
            'alert'        => trans('clumsy::alerts.token_mismatch'),
    	));
    }
});