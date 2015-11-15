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
     | Fail silently
     |--------------------------------------------------------------------------
     |
     | Whether to throw exceptions for errors like 404 or token mismatch or
     | handle them via redirects or error messages.
     |
     */

    'silent' => !config('app.debug'),

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

    'authentication-attribute' => 'email',

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
     | How many items of a given resource should be shown per page. You can
     | also override this on each model by defining an "admin_per_page"
     | property.
     |
     | To disable paging, set to 'false'.
     |
     */

    'per-page' => 40,


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

    'model-namespace'      => 'App',

    'controller-namespace' => 'App\\Http\\Controllers',

    'panel-namespace'      => 'App\\Panels',

    /*
     |--------------------------------------------------------------------------
     | Base target paths
     |--------------------------------------------------------------------------
     */

    'seed-path'         => database_path('seeds'),

    'factory-path'      => database_path('factories'),

    'views-folder-path' => base_path('resources/views/admin'),

];
