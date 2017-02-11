<div class="panel panel-default {{ $resource }}-panel">

@if (count($items))

    <div class="table-responsive">

        {!! csrf_field() !!}

        <table class="table {{ $resource }}-table"  data-update-url="{{ $updateUrl }}">
            <thead>
                <tr>
                @foreach ($columns as $column => $name)
                    <?php if (array_key_exists($column, $orderEquivalence)) $column = $orderEquivalence[$column]; ?>
                    <th class="{{ $panel->cellClass($items->first(), $column) }}">
                        {!! $sortable ? $panel->columnTitle($resource, $column, $name) : $name !!}
                    </th>
                @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach ($items as $item)
                @include($view->resolve('table-tr'))
            @endforeach
            </tbody>
        </table>
    </div>

@else

    @include($view->resolve('table-empty'))

@endif

</div>
