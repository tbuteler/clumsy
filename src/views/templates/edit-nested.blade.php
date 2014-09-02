@extends('clumsy::templates.edit')

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

        @include('clumsy::templates.table', array('items' => $children, 'columns' => $child_columns, 'resource' => $child_resource))

	    @if ($pagination)
	    	<div class="pull-right">
				{{ $pagination }}
	    	</div>
	    @endif

    @endif

@stop