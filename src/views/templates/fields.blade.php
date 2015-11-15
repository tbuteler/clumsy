@foreach ($columns as $column => $label)

	{!! field($column, $label) !!}

@endforeach