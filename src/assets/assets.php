<?php 

return array(

	'jquery' => array(
		'set'	=> 'footer',
		'path'	=> '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
	),

    'bootstrap.css' => array(
        'set'   => 'styles',
        'path'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css',
    ),
	
    'bootstrap.js' => array(
        'set'   => 'footer',
        'path'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js',
        'req'   => 'jquery',
    ),

    'tinymce' => array(
        'set'   => 'footer',
        'path'  => 'packages/clumsy/cms/js/libs/tinymce/tinymce.jquery.min.js',
        'req'   => 'jquery',
        'v'     => '4.0.28',
    ),

    'admin.css' => array(
        'set'   => 'styles',
        'path'  => 'packages/clumsy/cms/css/admin.css',
        'req'   => 'bootstrap.css',
        'v'     => '0.1',
    ),
    
    'admin.js' => array(
        'set'   => 'footer',
        'path'  => 'packages/clumsy/cms/js/admin.min.js',
        'req'   => 'bootstrap.js',
        'v'     => '0.1',
    ),
);