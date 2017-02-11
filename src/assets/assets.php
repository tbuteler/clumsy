<?php

$google_api = env('API_GOOGLE_MAPS', '');

return [

    'admin.css' => [
        'set'     => 'styles',
        'path'    => config('clumsy.cms.admin-css-path'),
        'hash'    => false,
        'version' => '0.29.0',
    ],

    'admin.js' => [
        'set'      => 'footer',
        'path'     => 'vendor/clumsy/cms/js/admin.js',
        'requires' => ['bootstrap.js', 'chosen'],
        'hash'     => false,
        'version'  => '0.29.0',
    ],

    'google-maps-admin' => [
        'set'   => 'footer',
        'path'  => "https://maps.googleapis.com/maps/api/js?key={$google_api}",
    ],
];
