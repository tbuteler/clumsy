<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" {!! !$showResource || $showResource === $resource ? 'class="active"' : '' !!}>
        <a href="#{{ $resource }}" aria-controls="{{ $resource }}" role="tab" data-toggle="tab">{{ $title or '' }}</a>
    </li>
    @if ($item->exists)
        @foreach ($panel->getChildren() as $child)
        <li role="presentation" {!! $showResource === $child->resourceName() ? 'class="active"' : '' !!}>
            <a href="#{{ $child->resourceName() }}" aria-controls="{{ $child->resourceName() }}" role="tab" data-toggle="tab">{{ $child->title }}</a>
        </li>
        @endforeach
    @endif
</ul>
