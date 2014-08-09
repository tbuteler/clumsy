<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;

/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
|
*/

App::error(function(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception)
{
	if (Config::get('cms::silent'))
	{
    	return Redirect::to('/');
	}
});

App::error(function(Illuminate\Session\TokenMismatchException $exception)
{
	if (Config::get('cms::silent'))
	{
    	return Redirect::back()->with(array(
        	'message' => trans('clumsy/cms::alerts.token_mismatch'),
    	));
    }
});