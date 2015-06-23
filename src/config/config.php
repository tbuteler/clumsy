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
     | Admin locale
     |--------------------------------------------------------------------------
     |
     | The admin locale can be set according to its own rule, if desired
     |
     */

    'admin_locale' => Config::get('app.locale'),

    /*
     |--------------------------------------------------------------------------
     | Default columns
     |--------------------------------------------------------------------------
     |
     | Which columns should be shown on resource tables on admin area, if
     | no columns are manually set
     |
     */

    'default_columns' => array('title' => trans('clumsy::fields.title')),

    /*
     |--------------------------------------------------------------------------
     | Default order of indexes
     |--------------------------------------------------------------------------
     |
     | Which columns should be used to sort items by default, if no other has
     | been defined by the user, and in which direction (asc vs. desc)
     |
     */

    'default_order' => array(

        'column'    => 'id',

        'direction' => 'desc',
    ),

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

    'per_page' => 50,


    /*
     |--------------------------------------------------------------------------
     | Generators
     |--------------------------------------------------------------------------
     |
     | Here you can configure namespaces and target folders for Clumsy's
     | boilerplate generation service
     |
     */

    /*
     |--------------------------------------------------------------------------
     | Object namespaces
     |--------------------------------------------------------------------------
     */
    
    'model_namespace'      => '',

    'controller_namespace' => '',

    /*
     |--------------------------------------------------------------------------
     | Base target paths
     |--------------------------------------------------------------------------
     */
        
    'model_path'        => app_path('models'),

    'controller_path'   => app_path('controllers'),

    'seed_path'         => app_path('database/seeds'),

    'views_folder_path' => app_path('views/admin'),


    /*
     |--------------------------------------------------------------------------
     | API Keys
     |--------------------------------------------------------------------------
     */
    
    'API_google_maps' => 'AIzaSyDTiJ_tH1-nnrKdnLcsKm54ovJGtCtmd_k',

);