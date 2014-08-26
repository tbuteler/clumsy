<?php

use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\URL;

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
Form::macro('delete', function($resource_type, $id) {

    $form_parameters = array(
        'method' => "DELETE",
        'url'    => URL::route("admin.$resource_type.destroy", $id),
        'class'  => "delete-form btn-outside pull-right $resource_type",
    );
 
    return Form::open($form_parameters)
            . Form::button('', array('type' => 'submit', 'title' => trans('clumsy/cms::buttons.delete'), 'class' => 'btn btn-danger glyphicon glyphicon-trash'))
            . Form::close();
});