@extends($view->resolve('master'))

@section('master')
    @include($view->resolve('edit-form'))

    @if ($item->exists && !$suppressDelete)

        {!! $panel->deleteButton() !!}

    @endif

@stop
