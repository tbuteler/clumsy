<?php

return array(

    'admin.css' => array(
        'set'   => 'styles',
        'path'  => 'packages/clumsy/cms/css/admin.css',
        'req'   => 'bootstrap',
        'v'     => '0.17.17',
    ),

    'admin.js' => array(
        'set'   => 'footer',
        'path'  => 'packages/clumsy/cms/js/admin.min.js',
        'req'   => array('bootstrap.js','chosen'),
        'v'     => '0.17.17',
    ),

    'google-maps' => array(
        'set'   => 'footer',
        'path'  => 'https://maps.googleapis.com/maps/api/js?key='.Config::get('clumsy::API_google_maps'),
    ),

    'google-maps-front-end' => array(
        'set'   => 'footer',
        'path'  => 'http://maps.google.com/maps/api/js?sensor=true&libraries=places,geometry',
    ),
);