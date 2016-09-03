<div class="thumbnail gallery-item {{ $panel->rowClass($item) }}">
    <a href="{{ route("$routePrefix.edit", $item->id) }}">
        {!! $panel->galleryThumbnail($item) !!}
    </a>
    <div class="caption">
        @foreach ($columns as $column => $name)
            @include($view->resolve('gallery-caption'))
        @endforeach
    </div>
</div>
