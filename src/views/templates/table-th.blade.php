<th class="{{ $panel->cellClass($items->first(), $column) }}">
    {!! $sortable ? HTML::columnTitle($resource, $column, $name) : $name !!}
</th>
