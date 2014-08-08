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
| Media upload button
|--------------------------------------------------------------------------
|
| This macro creates a button which allows users to upload media
| with drag and drop and AJAX functionality
|
|
*/
Form::macro('media', function($options = array())
{
    $defaults = array(
        'id'                => 'media',
        'label'             => 'Media',
        'association_type'  => null,
        'association_id'    => null,
        'position'          => null,
        'allow_multiple'    => false,
    );

    $options = array_merge($defaults, $options);

    extract($options, EXTR_SKIP);

    Asset::enqueue('media-management', 30);
    Asset::json('media', array(array($id, $allow_multiple)));

    $url = URL::route('media.upload', array(
        'object'   => (int)$association_id . '-' . $association_type,
        'position' => $position,
    ));

    $media = false;

    if ($association_id)
    {
        $media = Media::join('media_associations', 'media_associations.media_id', '=', 'media.id')
                      ->where('media_association_id', $association_id);
                      
        if ($association_type)
        {
            $media->where('media_association_type', $association_type);
        }

        if ($position)
        {
            $media->where('position', $position);
        }

        $media = $media->get();
    }
    else
    {
        if (Input::old('media-bind'))
        {
            $unbound = array();

            foreach (Input::old('media-bind') as $media_id => $attributes)
            {
                if ($attributes['position'] !== $position)
                {
                    continue;
                }

                $output .= Form::mediaBind($media_id, $position, $attributes['allow_multiple']);
                
                $unbound[] = $media_id;
            }

            if (sizeof($unbound))
            {
                $media = Media::whereIn('id', $unbound)->get();
            }
        }
    }

    $output = '<div class="form-group fileupload-group">';
    $output .= Form::label($id, $label);
    $output .= '<div id="' . $id . '" class="fileupload' . ($media && !$media->isEmpty() ? '' : ' empty') . '">';
    $output .= '<div class="fileupload-wrapper">';
    $output .= '
<div class="progress progress-striped active">
  <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
  </div>
</div>';

    if ($media)
    {
        $media->each(function($m) use(&$output, $id)
        {
            $output .= HTML::image($m->path, $id);
        });
    }
    
    $multiple = $allow_multiple ? ' multiple' : '';

    $output .= '
<div class="placeholders">
    <div class="glyphicon glyphicon-camera"></div>
    <div class="glyphicon glyphicon-plus"></div>
</div>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '<input id="' . $id . '-input" type="file" name="files[]" data-url="' . $url . '"' . $multiple . '>';
    $output .= '</div>';

    Event::listen('Print footer scripts', function() use($id, $label, $media)
    {
        echo HTML::mediaModal($id, $label, $media);
    });

    return $output;
});

/*
|--------------------------------------------------------------------------
| Media bind input
|--------------------------------------------------------------------------
|
| Creates hidden inputs to bind media to items which current don't exist
|
|
*/
Form::macro('mediaBind', function($media_id, $position = null, $allow_multiple = false)
{
    $output = Form::hidden('media-bind[' . $media_id . '][position]', $position);
    $output .= Form::hidden('media-bind[' . $media_id . '][allow_multiple]', $allow_multiple);

    return $output;
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
        'class'  => "delete-form pull-right $resource_type",
    );
 
    return Form::open($form_parameters)
            . Form::submit(trans('clumsy/cms::buttons.delete'), array('class' => 'btn btn-danger'))
            . Form::close();
});