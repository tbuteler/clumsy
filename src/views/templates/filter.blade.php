@if (isset($filtersData))

<div class="panel panel-default filter-panel collapse {{ $filtersData['hasFilters'] ? 'in' : '' }}" id="filter-collapse">
	<div class="panel-heading">
		<h4 class="panel-title">{{ trans('clumsy::titles.filters') }}</h4>
	</div>
	<table class="table filter-box">
		<tbody>
			@foreach ($filtersData['data'] as $column => $items)
				<tr>
					<th>{{ $filtersData['names'][$column] }}</th>
					<td>
					{{	Form::dropdown('filter_'.str_replace('.',':',$column), 
                            ' ', 
                            $items,
                            $filtersData['selected'][$column],
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
			<button id="filter-clear-btn" class="btn btn-default" type="button">
			{{ trans('clumsy::buttons.clear') }}
			</button>
			<button id="filter-submit-btn" class="btn btn-primary" type="button" disabled>
			{{ trans('clumsy::buttons.apply') }}</button>
		</div>
		<div class="clearfix"></div>
		{{ Form::open(array('url' => URL::route('clumsy.filter', $resource), 'id' => 'filter-form')) }}
		    @if (isset($filtersData['selected']))
		        @foreach ($filtersData['selected'] as $column => $values)
		            @if ($values != null) 
		                @foreach ($values as $value)
		                    {{ Form::hidden(str_replace('.',':',$column).'[]', $value) }}
		                @endforeach
		            @endif
		        @endforeach
		    @endif
	    {{ Form::close() }}
    </div>
</div>

@endif