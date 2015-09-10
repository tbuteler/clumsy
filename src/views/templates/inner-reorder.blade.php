@include($view->resolve('page-header'))

<div class="{{ $resource }}-panel">

@if (count($items))

{{ Form::open(array('url' => route('clumsy.save-active-reorder', $resource))) }}

<table class="reorder-table">
    <tbody>
        <?php $i=1; ?>
        @foreach ($items as $item)
            <tr>
                <td class="reorder-number">
                    {{ $i }}
                </td>
                <td class="reorder-item grabbable">
                    <i class="glyphicon glyphicon-menu-hamburger"></i>
                    <span>
                        @foreach ($columns as $column => $name)
                            {{ $item->$column }}
                        @endforeach
                        {{ Form::hidden('order[]', $item->id) }}
                    </span>
                </td>
            </tr>
        <?php $i++; ?>
        @endforeach
    </tbody>
</table>

<div class="bottom-buttons">
    {{ Form::button(trans('clumsy::buttons.save'), array('type' => 'submit', 'class' => 'btn btn-lg btn-success')) }}
</div>

{{ Form::close() }}

@else

    <div class="panel-body">
        <h5>
            {{ trans_choice('clumsy::alerts.count', 0, array('resource' => trans_choice('clumsy::alerts.items', 1), 'resources' => trans_choice('clumsy::alerts.items', 2))) }}
        </h5>
    </div>

@endif

</div>