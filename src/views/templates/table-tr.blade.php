<tr class="{{ $item->rowClass() }}">
@foreach ($columns as $column => $name)
    @include($view->resolve('table-td'))
@endforeach
</tr>