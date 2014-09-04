<div class="panel panel-default">

@if (count($items))

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            @foreach ($columns as $column => $name)
                <th>{{ $sortable ? sort_link($resource, $column, $name) : $name }}</th>
            @endforeach
            </thead>

            <tbody>
            @foreach ($items as $item)

                <tr>
                @foreach ($columns as $column => $name)

                    <?php

                    $type = 'text';
                    if (strpos($column, ':'))
                    {
                        list($column, $type) = explode(':', $column);
                    }
                    $value = $type === 'boolean' ? ($item->$column == 1 ? trans('clumsy::fields.yes') : trans('clumsy::fields.no')) : $item->$column;

                    ?>

                    <td>
                    @if (!isset($readonly) || !$readonly)
                    
                        <a href="{{ route("$admin_prefix.$resource.edit", $item->id); }}">{{ $value }}</a>
                    
                    @else
                    
                        {{ $value }}
                    
                    @endif
                    </td>

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