@extends('clumsy::templates.master')

@section('after-title')

    @if (isset($importer) && $importer)
        <a href="{{ route('import', $resource) }}">
            <button type="button" class="btn btn-primary add-new">{{ trans('clumsy::buttons.import', array('resources' => $display_name_plural)) }}</button>
        </a>
    @else
        <a href="{{ route("$admin_prefix.$resource.create") }}">
            <button type="button" class="btn btn-success add-new">{{ trans('clumsy::buttons.add') }}</button>
        </a>
    @endif

@stop

@section('master')

    @include('clumsy::templates.table')

    @if ($pagination)
        <div class="pull-right">
            {{ $pagination }}
        </div>
    @endif

@stop