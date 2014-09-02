@extends('clumsy::templates.master')

@section('master')

    @if (isset($item))

        {{ Form::model($item, array('method' => 'put', 'route' => array("$admin_prefix.$resource.update", $item->id), 'id' => 'main-form', 'autocomplete' => 'off')) }}

    @else

        {{ Form::open(array('url' => route("$admin_prefix.$resource.store"), 'id' => 'main-form')) }}

    @endif

    @include($form_fields)

    {{ $parent_field or '' }}

    <div class="bottom-buttons">
        {{ Form::submit(isset($item) ? trans('clumsy::buttons.save') : trans('clumsy::buttons.create'), array('class' => 'btn btn-lg btn-primary pull-left')) }}
    </div>

    {{ Form::close() }}

    @if (isset($item) && !isset($supress_delete))
    
        {{ Form::delete($resource, $item->id) }}

    @endif

@stop