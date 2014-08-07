<?php

/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
|
*/

App::error(function(Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception)
{
	if (\Config::get('cms::silent'))
	{
    	return Redirect::to('/');
	}
});

App::error(function(Illuminate\Session\TokenMismatchException $exception)
{
	if (\Config::get('cms::silent'))
	{
    	return Redirect::back()->with(array(
        	'message' => 'Your session expired before submitting changes. If you believe this is an error, please contact the website administrator.',
    	));
    }
});