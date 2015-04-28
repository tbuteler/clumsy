@if ($toggle_filters)

<ul class="nav nav-pills" role="tablist">
    <li role="presentation" {{ !$index_type ? 'class="active"' : '' }}>
    	<a href="{{ route("$admin_prefix.$resource.index") }}" role="tab">{{ isset($toggle_filters['all']) ? $toggle_filters['all'] : trans('clumsy::buttons.all_resources') }} <span class="badge">{{ $item_count['all'] }}</span></a>
    </li>
	@foreach ($toggle_filters as $filter => $filter_label)

		<?php if ($filter === 'all') continue; ?>

		<li role="presentation" {{ $index_type == $filter ? 'class="active"' : '' }}>
			<a href="{{ route("$admin_prefix.$resource.index-of-type", $filter) }}" role="tab">{{ $filter_label }} <span class="badge">{{ $item_count[$filter] }}</span></a>
		</li>
	@endforeach
</ul>

<hr>

@endif