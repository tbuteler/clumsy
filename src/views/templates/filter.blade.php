@if (isset($filterData) && $filterData)

<div class="panel panel-default filter-panel collapse {{ $filterData['hasFilters'] ? 'in' : '' }}" id="filter-collapse">
	{!! Form::open(['url' => route("$routePrefix.filter"), 'id' => 'filter-form']) !!}
	<div class="panel-heading">
		<h4 class="panel-title">{{ trans('clumsy::titles.filters') }}</h4>
	</div>
	<table class="table filter-box">
		<tbody>
			@foreach ($filterData['data'] as $column => $items)
				<tr>
					<th>{{ $filterData['names'][$column] }}</th>
					<td>
					{!!	dropdown()->noLabel()->options($items)->selected($filterData['selected'][$column])->multiple()->data('name', str_replace('.', ':', $column))->data('placeholder', ' ') !!}
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
	<div class="panel-footer clearfix">
		<div class="pull-right">
			<button id="filter-clear-btn" type="button" class="click-once btn btn-default">@lang('clumsy::buttons.clear')</button>
			<button id="filter-submit-btn" type="submit" class="click-once btn btn-primary" disabled>@lang('clumsy::buttons.apply')</button>
		</div>
	    @if (isset($filterData['selected']))
	        @foreach ($filterData['selected'] as $column => $values)
	            @if ($values != null)
	                @foreach ($values as $value)
	                    {!! Form::hidden(str_replace('.', ':', $column).'[]', $value) !!}
	                @endforeach
	            @endif
	        @endforeach
	    @endif
		@if ($isChild)
	    	{!! Form::hidden('query_string', http_build_query(['show' => $resource]), ['class' => 'filter-nested']) !!}
	    	{!! Form::hidden('parent_id', $id, ['class' => 'filter-nested']) !!}
	    @endif
    </div>
	{!! Form::close() !!}
</div>

@endif
