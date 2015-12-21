<div class="panel panel-default panel-translatable">
	<div class="panel-heading">
		<ul class="nav nav-pills" role="tablist">
		@foreach ($locales as $locale => $language)
			<li{!! $locale === $first ? ' class="active"' : '' !!}><a href="#{{ $locale }}" role="tab" data-toggle="pill">{{ $language['native'] }}</a></li>
		@endforeach
		</ul>
	</div>

	<div class="panel-body tab-content">
	@foreach ($locales as $locale => $language)
		<div class="tab-pane-translatable tab-pane{{ $locale === $first ? ' active' : '' }}" id="{{ $locale }}">
		@foreach ($translatable as $i => $field)
			@if ($field instanceof Clumsy\Utils\Library\Field)
				{!! $field->name($model->localizeColumn($fieldColumns[$i], $locale)) !!}
			@elseif ($field instanceof Illuminate\View\View)
				{!! $field->with(compact('locale'))->render() !!}
			@endif
		@endforeach
		</div>
	@endforeach
	</div>

</div>
