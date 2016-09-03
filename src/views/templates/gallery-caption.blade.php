@if (!$loop->first)
    <hr>
@endif
<span class="caption-{{ $column }}">{!! $panel->columnValue($item, $column) !!}</span>
