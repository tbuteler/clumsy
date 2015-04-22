@if (isset($filtersData))

<div class="panel panel-primary filter-panel" {{ $filtersData['hasFilters'] ? '' : 'style="display:none;"' }}>
	<div class="panel-heading" data-toggle="collapse" data-target="#filter-colapse">
		<h3 class="panel-title">{{ trans('clumsy::titles.filters') }}</h3>
	</div>
	<div class="collapse" id="filter-colapse">
		<div class="wrapper">
			<table class="table table-hover filter-box">
				<tbody>
					@foreach ($filtersData['data'] as $column => $items)
						<tr>
							<th>{{ $filtersData['names'][$column] }}</th>
							<td>
							{{	Form::dropdown('filter_'.str_replace('.',':',$column), 
			                                ' ', 
			                                array(null => '') + $items,
			                                $filtersData['selected'][$column],
			                                array('field' => array('data-name' => str_replace('.',':',$column), 'multiple' => 'multiple', 'data-placeholder' => 'Seleccionar...'))
			                            );
		                    }}
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
			<div class="pull-right">
				<button id="filter-clear-btn" class="btn btn-default" type="button">
				{{ trans('clumsy::buttons.clear') }}
				</button>
				<button id="filter-submit-btn" class="btn btn-primary" type="button" disabled>
				{{ trans('clumsy::buttons.apply') }}</button>
			</div>
			<div class="clearfix"></div>
			{{ Form::open(array('url' => URL::route('_filter', $resource),'id' => 'filter-form','style' => 'display:none;')) }}
			    @if (isset($filtersData['selected']))
			        @foreach ($filtersData['selected'] as $column => $values)
			            @if ($values != null) 
			                @foreach ($values as $value)
			                    {{ Form::text(str_replace('.',':',$column).'[]',$value) }}
			                @endforeach
			            @endif
			        @endforeach
			    @endif
		    {{ Form::close() }}
	    </div>
	</div>
</div>

@endif