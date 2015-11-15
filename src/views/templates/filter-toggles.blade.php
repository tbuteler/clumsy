@if ($toggleFilters)

<ul class="nav nav-pills" role="tablist">
    <li role="presentation" {!! !$indexType ? 'class="active"' : '' !!}>
    	<a href="{{ route("$routePrefix.index") }}" role="tab">{{ isset($toggleFilters['all']) ? $toggleFilters['all'] : trans('clumsy::buttons.all_resources') }} <span class="badge">{{ n($itemCount['all']) }}</span></a>
    </li>
	@foreach ($toggleFilters as $filter => $filterLabel)

		<?php if ($filter === 'all') continue; ?>

		<li role="presentation" {!! $indexType == $filter ? 'class="active"' : '' !!}>
			<a href="{{ route("$routePrefix.index-of-type", $filter) }}" role="tab">{{ $filterLabel }} <span class="badge">{{ n($itemCount[$filter]) }}</span></a>
		</li>
	@endforeach
</ul>

<hr>

@endif