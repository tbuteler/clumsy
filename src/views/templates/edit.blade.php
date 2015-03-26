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
        {{ Form::button(isset($item) ? trans('clumsy::buttons.save') : trans('clumsy::buttons.create'), array('type' => 'submit', 'class' => 'btn btn-lg btn-success')) }}
        @if (isset($item) && (!isset($suppress_delete) || !$suppress_delete))
            {{ Form::button('', array('type' => 'button', 'title' => trans('clumsy::buttons.delete'), 'class' => 'delete btn btn-lg btn-default glyphicon glyphicon-trash')) }}
        @endif
    </div>

    {{ Form::close() }}

    @if (isset($item) && (!isset($suppress_delete) || !$suppress_delete))
    
        {{ Form::delete($resource, $item->id) }}

    @endif

@stop