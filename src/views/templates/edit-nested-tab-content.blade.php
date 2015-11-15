<div class="tab-content">
    <div role="tabpanel" class="tab-pane {{ !$showResource || $showResource === $resource ? 'active' : '' }}" id="{{ $resource }}">
        @include($view->resolve('page-header'))
        @parent
    </div>
    @if ($item->exists)
        @foreach ($panel->getChildren() as $child)
        <div role="tabpanel" class="tab-pane child-resource-pane {{ $showResource === $child->resourceName() ? 'active' : '' }}" id="{{ $child->resourceName() }}">
            {!! $child->render() !!}
        </div>
        @endforeach
    @endif
</div>