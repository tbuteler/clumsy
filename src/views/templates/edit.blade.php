@extends($view->resolve('master'))

@section('master')
    @include($view->resolve('edit-form'))

    @if ($item->exists && !$suppressDelete)

        {!! Form::delete($resource, $item->id) !!}

    @endif

@stop
