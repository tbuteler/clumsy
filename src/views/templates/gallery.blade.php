<div class="row {{ $resource }}-gallery" data-update-url="{{ $updateUrl }}">

    {!! csrf_field() !!}

	@foreach ($items as $i => $item)
		<div class="col-md-{{ $columnSize }}">
			@include($view->resolve('gallery-item'))
		</div>

		@if (($i+1) % $columnsPerRow == 0)
			<div class="clearfix"></div>
		@endif

    @endforeach
</div>
