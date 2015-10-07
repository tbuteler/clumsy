<?php
$columns_per_row = $model->galleryColumnsPerRow();
$column_size = 12/$columns_per_row;
?>

<div class="row {{ $resource }}-gallery" data-model="{{ $model_class }}" data-resource="{{ $resource }}">

    {{ Form::token() }}

	@foreach ($items as $i => $item)
		<div class="col-md-{{ $column_size }}">
			<div class="thumbnail gallery-item {{ $item->rowClass() }}">
				<a href="{{ route("$admin_prefix.$resource.edit", $item->id) }}">
					{{ $item->galleryThumbnail() }}
				</a>
				<div class="caption">
					@foreach ($columns as $column => $name)
	                    <p>{{ $item->columnValue($column) }}</p>
	                @endforeach
				</div>
			</div>
		</div>

		@if (($i+1) % $columns_per_row == 0)
			<div class="clearfix"></div>
		@endif

    @endforeach
</div>