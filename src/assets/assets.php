<?php

$google_api = Config::get('clumsy::API_google_maps');

return array(

    'admin.css' => array(
        'set'   => 'styles',
        'path'  => 'packages/clumsy/cms/css/admin.css',
        'req'   => 'bootstrap',
        'v'     => '0.21.2',
    ),

    'admin.js' => array(
        'set'   => 'footer',
        'path'  => 'packages/clumsy/cms/js/admin.min.js',
        'req'   => array('bootstrap.js','chosen'),
        'v'     => '0.21.2',
    ),

    'google-maps' => array(
        'set'   => 'footer',
        'path'  => "https://maps.googleapis.com/maps/api/js?key={$google_api}",
    ),

    'google-maps-front-end' => array(
        'set'   => 'footer',
        'path'  => "http://maps.google.com/maps/api/js?key={$google_api}&sensor=true&libraries=places,geometry",
    ),
);
