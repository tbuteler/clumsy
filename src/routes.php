<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

Route::group(
    array(
        'namespace' => 'Clumsy\CMS\Controllers',
        'prefix'    => Config::get('clumsy::admin_prefix'),
        'before'    => 'clumsy:setPrefix',
    ),
    function()
    {
        /*
        |--------------------------------------------------------------------------
        | Admin authentication routes
        |--------------------------------------------------------------------------
        |
        */

        Route::get('login', array(
            'as'       => 'login',
            'before'   => 'clumsy:assets',
            'uses'     => 'AuthController@login',
        ));

        Route::post('login', array(
            'before'   => 'csrf',
            'uses'     => 'AuthController@postLogin',
        ));

        Route::get('reset', array(
            'as'       => 'reset-password',
            'before'   => 'clumsy:assets',
            'uses'     => 'AuthController@reset',
        ));

        Route::post('reset', array(
            'before'   => 'csrf',
            'uses'     => 'AuthController@postReset',
        ));

        Route::get('do-reset/{user_id}/{code}', array(
            'as'       => 'do-reset-password',
            'before'   => 'clumsy:assets',
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

        /*
        |--------------------------------------------------------------------------
        | Admin front-end routes
        |--------------------------------------------------------------------------
        |
        */

        Route::group(
            array(
                'before'    => 'clumsy|admin_extra',
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

                Route::get('{resource}/reorder',array(
                    'as'     => '_active-reorder',
                    'uses'   => 'AdminController@reorder',
                ));

                /*
                |--------------------------------------------------------------------------
                | User resource
                |--------------------------------------------------------------------------
                |
                */

                Route::resource('user', 'UsersController');
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Back-end routes
        |--------------------------------------------------------------------------
        |
        */

        Route::group(
            array(
                'before'    => 'clumsy:auth+user',
            ),
            function()
            {
                /*
                |--------------------------------------------------------------------------
                | External resource update routine
                |--------------------------------------------------------------------------
                |
                */

                Route::get('_import/{resource?}', array(
                    'as'   => '_import',
                    'uses' => 'ExternalResourceController@import',
                ));

                /*
                |--------------------------------------------------------------------------
                | Column sorting
                |--------------------------------------------------------------------------
                |
                */

                Route::get('_reorder/{resource}', array(
                    'as'   => '_reorder',
                    'uses' => 'BackEndController@reorder',
                ));

                /*
                |--------------------------------------------------------------------------
                | Index dynamic filters
                |--------------------------------------------------------------------------
                |
                */

                Route::post('_filter/{resource}', array(
                    'as'   => '_filter',
                    'uses' => 'BackEndController@filter',
                ));

                /*
                |--------------------------------------------------------------------------
                | AJAX entry updating (active booleans)
                |--------------------------------------------------------------------------
                |
                */

                Route::post('_update', array(
                    'as'     => '_update',
                    'before' => 'csrf',
                    'uses'   => 'BackEndController@update',
                ));

                /*
                |--------------------------------------------------------------------------
                | Active reorder
                |--------------------------------------------------------------------------
                |
                */

                Route::post('_reorder/{resource}',array(
                    'as'   => '_save-active-reorder',
                    'uses' => 'BackEndController@saveOrder',
                ));
            }
        );
    }
);