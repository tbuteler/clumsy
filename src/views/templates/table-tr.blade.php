<tr class="{{ $panel->rowClass($item) }}">
@foreach ($columns as $column => $name)
    @include($view->resolve('table-td'))
@endforeach
</tr>