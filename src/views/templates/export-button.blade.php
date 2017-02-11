@if (isset($exportLinks))
    @if (count($exportLinks) > 1)
        <div class="btn-group">
            <button id="export-dropdown-toggle" class="with-tooltip material-icons btn btn-default btn-icon btn-muted" title="{{ trans('clumsy::buttons.download') }}" data-toggle="dropdown">&#xE8D7;</button>
            <ul class="dropdown-menu export-dropdown-menu" role="menu" aria-labelledby="export-dropdown-toggle">
                @foreach ($exportLinks as $exportFormat => $exportUrl)
                <li>
                    <a href="{{ $exportUrl }}">{{ $exportFormat }}</a>
                </li>
                @endforeach
            </ul>
        </div>
    @else
        <a href="{{ $exportLinks->first() }}" class="with-tooltip material-icons btn btn-default btn-icon btn-muted" title="{{ trans('clumsy::buttons.download') }}">&#xE8D7;</a>
    @endif
@endif
