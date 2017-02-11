<?php

/*
 |--------------------------------------------------------------------------
 | Clumsy CMS settings
 |--------------------------------------------------------------------------
 |
 |
 */

return [

    /*
     |--------------------------------------------------------------------------
     | Authentication routes prefix
     |--------------------------------------------------------------------------
     |
     | URL prefix for the authentication URLs
     | i.e. http://example.com/admin/login
     |
     */

    'authentication-prefix' => 'admin',

    /*
     |--------------------------------------------------------------------------
     | Authentication middleware
     |--------------------------------------------------------------------------
     |
     | Additional middleware to use on default authentication routes
     |
     */

    'authentication-middleware' => ['web'],

    /*
     |--------------------------------------------------------------------------
     | Authentication model
     |--------------------------------------------------------------------------
     |
     | The model to use for user management within Clumsy.
     |
     */

    'authentication-model' => \Clumsy\CMS\Models\User::class,

    /*
     |--------------------------------------------------------------------------
     | Authentication attribute
     |--------------------------------------------------------------------------
     |
     | Which of the model's attributes, aside from password, should be used when
     | verifying credentials
     |
     */

    'authentication-attributes' => ['email'],

    /*
     |--------------------------------------------------------------------------
     | Paswword reset expiration time
     |--------------------------------------------------------------------------
     |
     | The expire time is the number of minutes that the reset token should be
     | considered valid. This security feature keeps tokens short-lived so
     | they have less time to be guessed. You may change this as needed.
     |
     */

    'password-reset-expiration' => 60,

    /*
     |--------------------------------------------------------------------------
     | Authentication throttling
     |--------------------------------------------------------------------------
     |
     | Throttling is an optional security feature for authentication, which
     | enables limiting of login attempts and the suspension & banning of users
     |
     */

    /*
     |--------------------------------------------------------------------------
     | Enable throttling
     |--------------------------------------------------------------------------
     */

    'authentication-throttling' => true,

    /*
     |--------------------------------------------------------------------------
     | Maximum attempts before lock-out
     |--------------------------------------------------------------------------
     */

    'throttling-max-attempts' => 5,

    /*
     |--------------------------------------------------------------------------
     | Lock-out duration, in seconds
     |--------------------------------------------------------------------------
     */

    'throttling-lockout-time' => 60,

    /*
     |--------------------------------------------------------------------------
     | Admin locale
     |--------------------------------------------------------------------------
     |
     | The admin locale can be set according to its own rule, if desired
     |
     */

    'admin-locale' => config('app.locale'),

    /*
     |--------------------------------------------------------------------------
     | Default order of indexes
     |--------------------------------------------------------------------------
     |
     | Which columns should be used to sort items by default, if no other has
     | been defined by the user, and in which direction (asc vs. desc)
     |
     */

    'default-order' => [
        'column'    => 'id',
        'direction' => 'desc',
    ],

    /*
     |--------------------------------------------------------------------------
     | Default items per page
     |--------------------------------------------------------------------------
     |
     | How many items of a given resource should be shown per page.
     |
     | To disable paging, set to false or null.
     | To inherit the models "perPage" property, set to 'inherit'.
     |
     */

    'per-page' => 'inherit',


    /*
     |--------------------------------------------------------------------------
     | Generators
     |--------------------------------------------------------------------------
     |
     | Here you can configure namespaces and target folders for Clumsy's
     | boilerplate generation service.
     |
     */

    /*
     |--------------------------------------------------------------------------
     | Object namespaces
     |--------------------------------------------------------------------------
     */

    'model-namespace'       => 'App',

    'pivot-trait-namespace' => 'App\\Relations',

    'controller-namespace'  => 'App\\Http\\Controllers',

    'panel-namespace'       => 'App\\Panels',

    /*
     |--------------------------------------------------------------------------
     | Base target paths
     |--------------------------------------------------------------------------
     */

    'seed-path'         => database_path('seeds'),

    'factory-path'      => database_path('factories'),

    'views-folder-path' => base_path('resources/views/admin'),

    /*
     |--------------------------------------------------------------------------
     | Default admin CSS path
     |--------------------------------------------------------------------------
     |
     | You can load any asset you want in Clumsy's admin area, but sometimes
     | you might want to extend Clumsy's default Sass files in order to easily
     | override certain variables before importing the file.
     |
     | The path is always relative to the public folder of your app.
     |
     */

    'admin-css-path' => 'vendor/clumsy/cms/css/admin.css',
];
