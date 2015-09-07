<th class="{{ with($items->first())->cellClass($column) }}">
    {{ $sortable ? HTML::columnTitle($resource, $column, $name) : $name }}
</th>