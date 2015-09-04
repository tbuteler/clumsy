<?php

/*
|--------------------------------------------------------------------------
| Clumsy boot filter
|--------------------------------------------------------------------------
|
*/

Route::filter('clumsy', '\Clumsy\CMS\Clumsy@boot');

/*
|--------------------------------------------------------------------------
| Backwards compatibility
|--------------------------------------------------------------------------
|
*/

Route::filter('admin_auth', '\Clumsy\CMS\Clumsy@auth');
Route::filter('admin_assets', '\Clumsy\CMS\Clumsy@assets');
Route::filter('admin_user', '\Clumsy\CMS\Clumsy@user');
