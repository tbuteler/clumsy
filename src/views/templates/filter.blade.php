@if (isset($filterData) && $filterData)

<div class="panel panel-default filter-panel collapse {{ $filterData['hasFilters'] ? 'in' : '' }}" id="filter-collapse">
	<div class="panel-heading">
		<h4 class="panel-title">{{ trans('clumsy::titles.filters') }}</h4>
	</div>
	<table class="table filter-box">
		<tbody>
			@foreach ($filterData['data'] as $column => $items)
				<tr>
					<th>{{ $filterData['names'][$column] }}</th>
					<td>
					{{	Form::dropdown('filter_'.str_replace('.',':',$column),
                            ' ',
                            $items,
                            $filterData['selected'][$column],
                            array('field' => array('data-name' => str_replace('.',':',$column), 'multiple' => 'multiple', 'data-placeholder' => ' '))
                        );
                    }}
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
	<div class="panel-footer">
		<div class="pull-right">
			<button id="filter-clear-btn" class="click-once btn btn-default">
			{{ trans('clumsy::buttons.clear') }}
			</button>
			<button id="filter-submit-btn" class="click-once btn btn-primary" disabled>
			{{ trans('clumsy::buttons.apply') }}</button>
		</div>
		<div class="clearfix"></div>
		{{ Form::open(array('url' => route('clumsy.filter', $resource), 'id' => 'filter-form')) }}
		    @if (isset($filterData['selected']))
		        @foreach ($filterData['selected'] as $column => $values)
		            @if ($values != null)
		                @foreach ($values as $value)
		                    {{ Form::hidden(str_replace('.', ':', $column).'[]', $value) }}
		                @endforeach
		            @endif
		        @endforeach
		    @endif
			@if ($is_child)
		    	{{ Form::hidden('query_string', http_build_query(array('show' => $resource)), array('class' => 'filter-nested')) }}
		    	{{ Form::hidden('parent_id', $id, array('class' => 'filter-nested')) }}
		    @endif
	    {{ Form::close() }}
    </div>
</div>

@endif