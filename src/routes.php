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

Route::group(
    array(
        'namespace' => 'Clumsy\CMS\Controllers',
        'prefix'    => Config::get('clumsy::admin_prefix'),
    ),
    function()
    {
        Route::get('login', array(
            'as'       => 'login',
            'before'   => 'admin_assets',
            'uses'     => 'AuthController@login',
        ));

        Route::post('login', array(
            'before'   => 'csrf',
            'uses'     => 'AuthController@postLogin',
        ));

        Route::get('reset', array(
            'as'       => 'reset-password',
            'before'   => 'admin_assets',
            'uses'     => 'AuthController@reset',
        ));

        Route::post('reset', array(
            'before'   => 'csrf',
            'uses'     => 'AuthController@postReset',
        ));

        Route::get('do-reset/{user_id}/{code}', array(
            'as'       => 'do-reset-password',
            'before'   => 'admin_assets',
            'uses'     => 'AuthController@doReset',
        ));

        Route::post('do-reset', array(
            'as'       => 'post-do-reset-password',
            'before'   => 'csrf',
            'uses'     => 'AuthController@postDoReset',
        ));

        Route::get('logout', array(
            'as'       => 'logout',
            'uses'     => 'AuthController@logout',
        ));
    }
);

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

        Route::get('{resource}/reorder',array(
            'as'     => '_reorder',
            'uses'   => 'AdminController@reorder',
        ));

        Route::post('_reorder/{resource}',array(
            'as'   => '_save-reorder',
            'uses' => 'BackEndController@saveOrder',
        ));

        Route::post('_filter/{resource}', array(
            'as'   => '_filter',
            'uses' => 'BackEndController@filter',
        ));

        Route::post('_update', array(
            'as'     => '_update',
            'before' => 'csrf',
            'uses'   => 'BackEndController@update',
        ));

        Route::resource('user', 'UsersController');
    }
);