<?php

$google_api = config('clumsy.utils.api-google-maps');

return [

    'admin.css' => [
        'set'    => 'styles',
        'path'   => 'vendor/clumsy/cms/css/admin.css',
        'req'    => 'bootstrap',
        'v'      => '0.26.1',
        'elixir' => false,
    ],

    'admin.js' => [
        'set'    => 'footer',
        'path'   => 'vendor/clumsy/cms/js/admin.min.js',
        'req'    => ['bootstrap.js', 'chosen'],
        'v'      => '0.26.1',
        'elixir' => false,
    ],

    'google-maps-admin' => [
        'set'   => 'footer',
        'path'  => "https://maps.googleapis.com/maps/api/js?key={$google_api}",
    ],
];
