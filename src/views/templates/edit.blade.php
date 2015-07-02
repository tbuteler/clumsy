@extends('clumsy::templates.master')

@section('master')
    @include($view->resolve('edit-form'))

    @if ($item->exists && (!isset($suppress_delete) || !$suppress_delete))

        {{ Form::delete($resource, $item->id) }}

    @endif

@stop