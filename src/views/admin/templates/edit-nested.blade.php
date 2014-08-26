@extends('clumsy/cms::admin.templates.edit')

@section('after-content')

    @if (isset($item))

        <h1 class="page-header">

            {{ $children_title }}

            <a href="{{ $add_child }}"><button type="button" class="btn btn-success btn-title pull-right add-new">Add new</button></a>
        </h1>

        @include('clumsy/cms::admin.templates.table', array('items' => $children, 'properties' => $child_properties, 'resource' => $child_resource))

    @endif

@stop