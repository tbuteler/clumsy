<tr class="{{ $panel->rowClass($item) }}">
@foreach ($columns as $column => $name)
    <td class="{{ $panel->cellClass($item, $column) }}">{!! $panel->columnValue($item, $column) !!}</td>
@endforeach
</tr>
