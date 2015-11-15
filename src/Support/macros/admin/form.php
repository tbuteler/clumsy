<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Clumsy\CMS\Facades\Clumsy;
use Clumsy\Assets\Facade as Asset;

/*
|--------------------------------------------------------------------------
| Delete button
|--------------------------------------------------------------------------
|
| This macro creates a form with only a submit button.
| We'll use it to generate forms that will post to a certain url with the
| DELETE method, following REST principles.
|
*/

Form::macro('delete', function ($resource_type, $id) {

    $prefix = Clumsy::prefix();
    $form_parameters = [
        'method' => "DELETE",
        'url'    => route("$prefix.$resource_type.destroy", $id),
        'class'  => "delete-form btn-outside pull-left $resource_type",
    ];

    return Form::open($form_parameters).Form::close();
});

Form::macro('location', function ($lat, $lng, $address = null) {

    Asset::enqueue('google-maps-admin');

    return View::make('clumsy::macros.location', compact('lat', 'lng', 'address'))->render();
});
