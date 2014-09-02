@foreach ($columns as $column => $label)

	{{ Form::field($column, $label) }}

@endforeach