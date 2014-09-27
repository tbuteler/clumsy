<div class="panel panel-default {{ $resource }}-panel">

@if (count($items))

    <div class="table-responsive">

        {{ Form::token() }}

        <table class="table {{ $resource }}-table">
            <thead>
            @foreach ($columns as $column => $name)
                <?php if (array_key_exists($column, $order_equivalence)) $column = $order_equivalence[$column]; ?>
                <th class="{{ with($items->first())->cellClass($column) }}">{{ $sortable ? HTML::columnTitle($resource, $column, $name) : $name }}</th>
            @endforeach
            </thead>

            <tbody>
            @foreach ($items as $item)
                <tr class="{{ $item->rowClass() }}">
                @foreach ($columns as $column => $name)
                    <td class="{{ $item->cellClass($column) }}">{{ $item->columnValue($column) }}</td>
                @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@else

    <div class="panel-body">
        <h5>
            {{ trans_choice('clumsy::alerts.count', 0, array('resource' => trans_choice('clumsy::alerts.items', 1), 'resources' => trans_choice('clumsy::alerts.items', 2))) }}
        </h5>
    </div>

@endif

</div>