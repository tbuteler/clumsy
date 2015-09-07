<div class="panel panel-default {{ $resource }}-panel">

@if (count($items))

    <div class="table-responsive">

        {{ Form::token() }}

        <table class="table {{ $resource }}-table" data-model="{{ $model_class }}" data-resource="{{ $resource }}">
            <thead>
            @foreach ($columns as $column => $name)
                <?php if (array_key_exists($column, $order_equivalence)) $column = $order_equivalence[$column]; ?>
                @include($view->resolve('table-th'))
            @endforeach
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