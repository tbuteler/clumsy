<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
*/

Route::get(Config::get('cms::admin_prefix').'/login', array(
    'as'       => 'login',
    'before'   => 'admin_assets',
    'uses'     => 'Clumsy\CMS\Controllers\AuthController@login',
));

Route::post(Config::get('cms::admin_prefix').'/login', array(
    'uses'     => 'Clumsy\CMS\Controllers\AuthController@postLogin',
));

Route::get(Config::get('cms::admin_prefix').'/logout', array(
    'as'       => 'logout',
    'uses'     => 'Clumsy\CMS\Controllers\AuthController@logout',
));

Route::group(
    array(
        'prefix' => Config::get('cms::admin_prefix'),
        'before' => 'admin_auth|admin_assets|admin_extra',
    ),
    function()
    {
        Route::get('/', array(
            'as'   => Config::get('cms::admin_prefix').'.home',
            function()
            {
                return View::make('clumsy/cms::admin.index');
            }
        ));

        Route::get('import/{resource?}', array(
            'as'   => 'import',
            'uses' => 'Clumsy\CMS\Controllers\ExternalResourceController@import',
        ));

        Route::resource('user', 'Clumsy\CMS\Controllers\UsersController');
    }
);