@include($view->resolve('page-header'))

<div class="{{ $resource }}-panel">

@if (count($items))

@form(['url' => route("$routePrefix.update-order")])

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

                            @hidden('order[]', $item->id)

                        </span>
                    </td>
                </tr>
            <?php $i++; ?>
            @endforeach
        </tbody>
    </table>

    @include($view->resolve('bottom-buttons'))

@endform

@else

    @include($view->resolve('table-empty'))

@endif

</div>
