<div class="panel panel-default">
	<div class="panel-heading">
		<ul class="nav nav-pills" role="tablist">
		@foreach ($locales as $locale => $language)
			<li{{ $locale === $first ? ' class="active"' : '' }}><a href="#{{ $locale }}" role="tab" data-toggle="pill">{{ $language['native'] }}</a></li>
		@endforeach
		</ul>
	</div>

	<div class="panel-body tab-content">
	@foreach ($locales as $locale => $language)
		<div class="tab-pane{{ $locale === $first ? ' active' : '' }}" id="{{ $locale }}">
		@foreach ($fields as $column => $label)
		<?php
			if(str_contains($column, ':'))
			{
				list($column, $type) = explode(':', $column);
			}
			else
			{
				$type = 'field';
			}

			if ($type == 'media') {
				$newColumn = $column.'_'.$locale;
		?>
				{{ Form::$type($label[$newColumn]) }}		
		<?php
			}
			else{
		?>
				{{ Form::$type($column.'_'.$locale, $label) }}
		<?php
			}
		?>
		@endforeach
		</div>
	@endforeach
	</div>

</div>