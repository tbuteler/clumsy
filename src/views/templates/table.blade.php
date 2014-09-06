<div class="panel panel-default">

@if (count($items))

    <div class="table-responsive">
        <table class="table">
            <thead>
            @foreach ($columns as $column => $name)
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
            {{ trans_choice('clumsy::alerts.count', 0, array('resource' => $display_name, 'resources' => $display_name_plural)) }}
        </h5>
    </div>

@endif

</div>