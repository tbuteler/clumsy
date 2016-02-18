@if ($toggleFilters)

<ul class="nav nav-pills" role="tablist">
	@foreach ($toggleFilters as $filter => $filterLabel)
		<li role="presentation" {!! $indexType == $filter ? 'class="active"' : '' !!}>
			<a href="{{ $panel->toggleUrl($filter) }}" role="tab">{{ $filterLabel }} <span class="badge">{{ n($itemCount[$filter]) }}</span></a>
		</li>
	@endforeach
</ul>

<hr>

@endif
