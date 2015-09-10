<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" {{ !$show_resource || $show_resource === $resource ? 'class="active"' : '' }}>
        <a href="#{{ $resource }}" aria-controls="{{ $resource }}" role="tab" data-toggle="tab">{{ $title or '' }}</a>
    </li>
    @if ($item->exists)
        @foreach ($children as $child_resource => $child)
            <li role="presentation" {{ $show_resource === $child_resource ? 'class="active"' : '' }}>
                <a href="#{{ $child_resource }}" aria-controls="{{ $child_resource }}" role="tab" data-toggle="tab">{{ $child['tab_title'] or $child['title'] }}</a>
            </li>
        @endforeach
    @endif
</ul>