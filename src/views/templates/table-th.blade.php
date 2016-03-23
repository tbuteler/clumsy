<th class="{{ $panel->cellClass($items->first(), $column) }}">
    {!! $sortable ? $panel->columnTitle($resource, $column, $name) : $name !!}
</th>
