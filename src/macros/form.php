<?php

/*
|--------------------------------------------------------------------------
| Boolean
|--------------------------------------------------------------------------
|
| Checkbox with auxiliary HTML
|
*/
Form::macro('boolean', function($name, $label)
{
    $output = '<div class="checkbox">';
    $output .= '<label for="' . $name . '">';
    $output .= Form::checkbox($name, 1, null, array('id' => $name)) . $label;
    $output .= '</label>';
    $output .= '</div>';

    return $output;
});

/*
|--------------------------------------------------------------------------
| Dropdown
|--------------------------------------------------------------------------
|
| Select input with auxiliary HTML
|
*/
Form::macro('dropdown', function($name, $label, $values, $selected = null, $field_attributes = array('class' => 'form-control'))
{
    $output = '<div class="form-group">';
    $output .= Form::label($name, $label);
    $output .= Form::select($name, $values, $selected, $field_attributes);
    $output .= '</div>';

    return $output;
});

/*
|--------------------------------------------------------------------------
| Field
|--------------------------------------------------------------------------
|
| Versatile input macro with auxiliary HTML and error feedback
|
*/
Form::macro('field', function($name, $label, $type = 'text', $field_attributes = array('class' => 'form-control'))
{
    $help = null;

    $class = array('form-group');

    if (Session::has('errors')) {

        $errors = Session::get('errors');

        if ($errors->has($name)) {

            $class[] = 'has-error';
            $class[] = 'has-feedback';

            $help = '<span class="glyphicon glyphicon-remove form-control-feedback"></span>';
            $help .= '<p class="help-block">' . $errors->first($name) . '</p>';
        }
    }

    $class = implode(' ', $class);

    $output = "<div class=\"$class\">";
    $output .= Form::label($name, $label);

    if (in_array($type, array('password', 'file'))) {
    
        $output .= Form::$type($name, $field_attributes);
    
    } else {

        $output .= Form::$type($name, $value = null, $field_attributes);        
    }

    $output .= $help;
    $output .= '</div>';
    
    return $output;
});

/*
|--------------------------------------------------------------------------
| Rich Text
|--------------------------------------------------------------------------
|
| Shorthand for calling Field macro while enqueuing RTE scripts
|
*/
Form::macro('richText', function($name, $label)
{
    Asset::enqueue('tinymce');

    return Form::field($name, $label, 'textarea', array('class' => 'form-control rich-text'));
});

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
            . Form::submit(trans('clumsy/cms::buttons.delete'), array('class' => 'btn btn-danger'))
            . Form::close();
});