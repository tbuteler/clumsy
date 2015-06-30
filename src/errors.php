<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Clumsy\CMS\Facades\Clumsy;

/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
|
*/

App::error(function(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception)
{
    Log::error('NotFoundHttpException Route: '.Request::url());

	if (Config::get('clumsy::silent'))
	{
    	return Redirect::to('/');
	}
});

App::error(function(Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $exception)
{
    Log::error('MethodNotAllowedHttpException Route: '.Request::url());
    Log::error($exception);
});

App::error(function(Illuminate\Session\TokenMismatchException $exception)
{
    Log::error('TokenMismatchException Route: '.Request::url());
    Log::error(Input::all());
    Log::error($exception);

	if (Config::get('clumsy::silent') && Route::getCurrentRoute()->getPrefix() === Clumsy::prefix())
	{
    	return Redirect::back()->with(array(
            'alert_status' => 'warning',
            'alert'        => trans('clumsy::alerts.token_mismatch'),
    	));
    }
});