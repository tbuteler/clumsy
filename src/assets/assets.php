<?php

$google_api = Illuminate\Support\Facades\Config::get('clumsy/utils::api-google-maps');

return array(

    'admin.css' => array(
        'set'   => 'styles',
        'path'  => 'packages/clumsy/cms/css/admin.css',
        'req'   => 'bootstrap',
        'v'     => '0.22.5',
    ),

    'admin.js' => array(
        'set'   => 'footer',
        'path'  => 'packages/clumsy/cms/js/admin.min.js',
        'req'   => array('bootstrap.js','chosen'),
        'v'     => '0.22.5',
    ),

    'google-maps-admin' => array(
        'set'   => 'footer',
        'path'  => "https://maps.googleapis.com/maps/api/js?key={$google_api}",
    ),
);
