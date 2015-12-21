@extends($view->resolve('edit'))

@section('page-header')
@stop

@section('master')
<div role="tabpanel">

    @include($view->resolve('edit-nested-tablist'))
    @include($view->resolve('edit-nested-tab-content'))

</div>
@stop
