@extends('clumsy::templates.edit')

@section('after-content')

    @if ($item->exists)

	    <h1 class="page-header">
	        <div class="row">
				<div class="col-sm-9">
		        	{{ $children_title }}
		        </div>
				<div class="col-sm-3 after-title">
		        	<a href="{{ $add_child }}" class="btn btn-success add-new">{{ trans('clumsy::buttons.add') }}</a>
				</div>
	    	</div>
	    </h1>

        @include('clumsy::templates.table', array('items' => $children, 'columns' => $child_columns, 'resource' => $child_resource))

    	@include('clumsy::templates.pagination')

    @endif

@stop