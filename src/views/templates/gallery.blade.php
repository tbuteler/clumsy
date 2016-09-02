<div class="row {{ $resource }}-gallery" data-update-url="{{ $updateUrl }}">

    {!! csrf_field() !!}

        @if (count($items))

        @foreach ($items as $i => $item)
            <div class="col-md-{{ $columnSize }}">
                <div class="thumbnail gallery-item {{ $panel->rowClass($item) }}">
                    <a href="{{ route("$routePrefix.edit", $item->id) }}">
                        {!! $panel->galleryThumbnail($item) !!}
                    </a>
                    <div class="caption">
                        @foreach ($columns as $column => $name)
                            <p>{!! $panel->columnValue($item, $column) !!}</p>
                        @endforeach
                    </div>
                </div>
            </div>

            @if (($i+1) % $columnsPerRow == 0)
                <div class="clearfix"></div>
            @endif

        @endforeach

    @else

        <div class="panel panel-default">
            @include($view->resolve('table-empty'))
        </div>

    @endif

</div>
