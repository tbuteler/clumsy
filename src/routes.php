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

Route::get(Config::get('clumsy::admin_prefix').'/login', array(
    'as'       => 'login',
    'before'   => 'admin_assets',
    'uses'     => 'Clumsy\CMS\Controllers\AuthController@login',
));

Route::post(Config::get('clumsy::admin_prefix').'/login', array(
    'before'   => 'csrf',
    'uses'     => 'Clumsy\CMS\Controllers\AuthController@postLogin',
));

Route::get(Config::get('clumsy::admin_prefix').'/reset', array(
    'as'       => 'reset-password',
    'before'   => 'admin_assets',
    'uses'     => 'Clumsy\CMS\Controllers\AuthController@reset',
));

Route::post(Config::get('clumsy::admin_prefix').'/reset', array(
    'before'   => 'csrf',
    'uses'     => 'Clumsy\CMS\Controllers\AuthController@postReset',
));

Route::get(Config::get('clumsy::admin_prefix').'/do-reset/{user_id}/{code}', array(
    'as'       => 'do-reset-password',
    'before'   => 'admin_assets',
    'uses'     => 'Clumsy\CMS\Controllers\AuthController@doReset',
));

Route::post(Config::get('clumsy::admin_prefix').'/do-reset', array(
    'as'       => 'post-do-reset-password',
    'before'   => 'csrf',
    'uses'     => 'Clumsy\CMS\Controllers\AuthController@postDoReset',
));

Route::get(Config::get('clumsy::admin_prefix').'/logout', array(
    'as'       => 'logout',
    'uses'     => 'Clumsy\CMS\Controllers\AuthController@logout',
));

Route::group(
    array(
        'namespace' => 'Clumsy\CMS\Controllers',
        'prefix'    => Config::get('clumsy::admin_prefix'),
        'before'    => 'admin_auth|admin_user|admin_assets|admin_extra',
    ),
    function()
    {
        Route::get('/', array(
            'as'   => Config::get('clumsy::admin_prefix').'.home',
            function()
            {
                return View::make('clumsy::index');
            }
        ));

        Route::get('_import/{resource?}', array(
            'as'   => '_import',
            'uses' => 'ExternalResourceController@import',
        ));

        Route::get('_reorder/{resource}', array(
            'as'   => '_reorder',
            'uses' => 'BackEndController@reorder',
        ));

        Route::post('_update', array(
            'as'     => '_update',
            'before' => 'csrf',
            'uses'   => 'BackEndController@update',
        ));

        Route::resource('user', 'UsersController');
    }
);