{{ Form::model($item, array('method' => ($item->exists ? 'put' : 'post'), 'route' => ($item->exists ? array("$admin_prefix.$resource.update", $item->id) : "$admin_prefix.$resource.store"), 'id' => 'main-form', 'autocomplete' => 'off')) }}

@include($view->resolve('fields'))

@foreach ($fields as $field)
    {{ $field }}
@endforeach

@include($view->resolve('bottom-buttons'))

{{ Form::close() }}