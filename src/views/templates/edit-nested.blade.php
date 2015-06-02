@extends($view->resolve('edit'))

@section('inner-title')
@stop

@section('master')
<div role="tabpanel">

    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
            <a href="#{{ $resource }}" aria-controls="{{ $resource }}" role="tab" data-toggle="tab">{{ $title or '' }}</a>
        </li>
        @if ($item->exists)
        <li role="presentation">
            <a href="#{{ $child_resource }}" aria-controls="{{ $child_resource }}" role="tab" data-toggle="tab">{{ $children_title }}</a>
        </li>
        @endif
    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="{{ $resource }}">
            <h1 class="page-header">
                {{ $title }}
            </h1>
            @parent
        </div>
        @if ($item->exists)
        <div role="tabpanel" class="tab-pane" id="{{ $child_resource }}">
            <h1 class="page-header">
                <div class="row">
                    <div class="col-sm-9">
                        {{ $children_title }}
                    </div>
                    <div class="col-sm-3 after-title">
                        <a href="{{ $add_child }}" class="btn btn-success add-new">{{ trans('clumsy::buttons.add') }}</a>
                    </div>
                </div>
            </h1>
            @include($view->resolve('inner-index', $child_resource), array('title' => $children_title, 'items' => $children, 'columns' => $child_columns, 'resource' => $child_resource))
        </div>
        @endif
    </div>

</div>
@stop