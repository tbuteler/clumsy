@extends('clumsy/cms::admin.templates.edit')

@section('after-content')

    @if (isset($item))

	    <h1 class="page-header">
	        <div class="row">
				<div class="col-sm-9">
		        	{{ $children_title }}
		        </div>
				<div class="col-sm-3 after-title">
		        	<a href="{{ $add_child }}"><button type="button" class="btn btn-success add-new">Add new</button></a>
				</div>
	    	</div>
	    </h1>

        @include('clumsy/cms::admin.templates.table', array('items' => $children, 'properties' => $child_properties, 'resource' => $child_resource))

    @endif

@stop