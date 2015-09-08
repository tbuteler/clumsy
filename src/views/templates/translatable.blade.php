<div class="panel panel-default panel-translatable">
	<div class="panel-heading">
		<ul class="nav nav-pills" role="tablist">
		@foreach ($locales as $locale => $language)
			<li{{ $locale === $first ? ' class="active"' : '' }}><a href="#{{ $locale }}" role="tab" data-toggle="pill">{{ $language['native'] }}</a></li>
		@endforeach
		</ul>
	</div>

	<div class="panel-body tab-content">
	@foreach ($locales as $locale => $language)
		<div class="tab-pane-translatable tab-pane{{ $locale === $first ? ' active' : '' }}" id="{{ $locale }}">
		@foreach ($fields as $column => $label)

			@if(str_contains($column, ':'))
				<?php list($column, $type) = explode(':', $column); ?>
			@else
				<?php $type = 'field'; ?>
			@endif

			@if ($type === 'media')
				{{ Form::$type($label[$model->localizeColumn($column, $locale)]) }}
			@else
				{{ Form::$type($model->localizeColumn($column, $locale), $label) }}
			@endif

		@endforeach
		</div>
	@endforeach
	</div>

</div>