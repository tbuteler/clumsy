@extends('clumsy::templates.master')

@section('after-title')

    @if (isset($importer) && $importer)
        <a href="{{ route('_import', $resource) }}">
            <button type="button" class="btn btn-primary add-new">{{ trans('clumsy::buttons.import', array('resources' => $model->displayNamePlural())) }}</button>
        </a>
    @else
        <a href="{{ route("$admin_prefix.$resource.create") }}">
            <button type="button" class="btn btn-success add-new">{{ trans('clumsy::buttons.add') }}</button>
        </a>
    @endif

@stop

@section('master')

    @if (isset($filtersData))
        @include('clumsy::templates.filter')
    @endif

    @include('clumsy::templates.table')

    @if ($pagination)
        <div class="pull-right">
            {{ $pagination }}
        </div>
    @endif

@stop