@extends('clumsy::templates.master')

@section('master')

    {{ Form::model($item, array('method' => ($item->exists ? 'put' : 'post'), 'route' => ($item->exists ? array("$admin_prefix.$resource.update", $item->id) : "$admin_prefix.$resource.store"), 'id' => 'main-form', 'autocomplete' => 'off')) }}

    @include($form_fields)

    @foreach ($fields as $field)
        {{ $field }}
    @endforeach

    <div class="bottom-buttons">
        {{ Form::button(isset($item) ? trans('clumsy::buttons.save') : trans('clumsy::buttons.create'), array('type' => 'submit', 'class' => 'btn btn-lg btn-success')) }}
        @if ($item->exists && (!isset($suppress_delete) || !$suppress_delete))
            {{ Form::button('', array('type' => 'button', 'title' => trans('clumsy::buttons.delete'), 'class' => 'delete btn btn-lg btn-default glyphicon glyphicon-trash')) }}
        @endif
    </div>

    {{ Form::close() }}

    @if ($item->exists && (!isset($suppress_delete) || !$suppress_delete))

        {{ Form::delete($resource, $item->id) }}

    @endif

@stop