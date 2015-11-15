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

App::pushError(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception) {

    if (config('clumsy.silent')) {
        return Redirect::to('/');
    }
});

App::pushError(function (Illuminate\Session\TokenMismatchException $exception) {

    if (Config::get('clumsy::silent') && isAdmin()) {
        return back()->withAlert([
            'warning' => trans('clumsy::alerts.token_mismatch'),
        ]);
    }
});
