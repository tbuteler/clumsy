@extends('clumsy/cms::admin.templates.master')

@section('master')

    @if (isset($item))

        {{ Form::model($item, array('method' => 'put', 'route' => array("admin.$resource.update", $item->id), 'id' => 'main-form', 'autocomplete' => 'off')) }}

    @else

        {{ Form::open(array('url' => route("admin.$resource.store"), 'id' => 'main-form')) }}

    @endif

    @include($form_fields)

    <div class="bottom-buttons">
        {{ Form::submit(isset($item) ? trans('clumsy/cms::buttons.save') : trans('clumsy/cms::buttons.create'), array('class' => 'btn btn-primary')) }}
    </div>

    {{ Form::close() }}

    @if (isset($item) && !isset($supress_delete))
    
        {{ Form::delete($resource, $item->id) }}

    @endif

@stop