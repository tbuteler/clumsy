{!! Form::model($item, ['method' => ($item->exists ? 'put' : 'post'), 'route' => ($item->exists ? ["$adminPrefix.$resource.update", $item->id] : "$routePrefix.store"), 'id' => 'main-form', 'autocomplete' => 'off']) !!}

@include($view->resolve('fields'))

@foreach ($fields as $field)
    {!! $field !!}
@endforeach

@include($view->resolve('bottom-buttons'))

{!! Form::close() !!}