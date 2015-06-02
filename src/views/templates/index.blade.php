@extends('clumsy::templates.master')

@section('after-title')

    @if (isset($importer) && $importer)
        <a href="{{ route('_import', $resource) }}" class="btn btn-primary add-new">{{ trans('clumsy::buttons.import', array('resources' => $model->displayNamePlural())) }}</a>
    @else
        <a href="{{ route("$admin_prefix.$resource.create") }}" class="btn btn-success add-new">{{ trans('clumsy::buttons.add') }}</a>
    @endif

@stop

@section('master')

    @include($view->resolve('inner-index'))

@stop