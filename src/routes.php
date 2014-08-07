<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
*/

Route::get(\Config::get('cms::admin_prefix').'/login', array(
    'as'     => 'login',
    'before' => 'admin_assets',
    'uses'   => 'Clumsy\CMS\Controllers\AuthController@login',
));

Route::post(\Config::get('cms::admin_prefix').'/login', array(
    'uses'   => 'Clumsy\CMS\Controllers\AuthController@postLogin',
));

Route::get(\Config::get('cms::admin_prefix').'/logout', array(
    'as'     => 'logout',
    'uses'   => 'Clumsy\CMS\Controllers\AuthController@logout',
));

Route::group(array('prefix' => \Config::get('cms::admin_prefix'), 'before' => 'admin_auth|admin_assets|admin_extra'), function()
{
    Route::get('import/{resource?}', array(
        'as'    => 'import',
        'uses'  => 'Clumsy\CMS\Controllers\ExternalResourceController@import',
    ));

    Route::resource('user', 'Clumsy\CMS\Controllers\UsersController');
});