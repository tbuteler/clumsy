<?php

/*
 |--------------------------------------------------------------------------
 | Clumsy CMS settings
 |--------------------------------------------------------------------------
 |
 |
 */

return array(

    /*
     |--------------------------------------------------------------------------
     | Fail silently
     |--------------------------------------------------------------------------
     |
     | Whether to throw exceptions for errors like 404 or token mismatch or
     | handle them via redirects or error messages.
     |
     */

    'silent' => !Config::get('app.debug'),

    /*
     |--------------------------------------------------------------------------
     | Admin prefix
     |--------------------------------------------------------------------------
     |
     | URL prefix that points to the admin area of your CMS
     | i.e. http://example.com/admin/
     |
     */

	'admin_prefix' => 'admin',

    /*
     |--------------------------------------------------------------------------
     | Default properties
     |--------------------------------------------------------------------------
     |
     | Which columns should be shown on resource tables on admin area, if
     | no properties are set
     |
     */

    'default_columns' => array('title' => trans('clumsy/cms::fields.title')),
);