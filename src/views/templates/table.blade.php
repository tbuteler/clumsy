<div class="panel panel-default">

@if (count($items))

    <div class="table-responsive">
        <table class="table">
            <thead>
            @foreach ($columns as $column => $name)
                <?php if (array_key_exists($column, $order_equivalence)) $column = $order_equivalence[$column]; ?>
                <th>{{ $sortable ? HTML::columnTitle($resource, $column, $name) : $name }}</th>
            @endforeach
            </thead>

            <tbody>
            @foreach ($items as $item)
                <tr>
                @foreach ($columns as $column => $name)
                    <td>{{ $item->columnValue($column) }}</td>
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