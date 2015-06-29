<div class="tab-content">
    <div role="tabpanel" class="tab-pane {{ !$show_resource || $show_resource === $resource ? 'active' : '' }}" id="{{ $resource }}">
        @include($view->resolve('page-header'))
        @parent
    </div>
    @if ($item->exists)
    <div role="tabpanel" class="tab-pane child-resource-pane {{ $show_resource === $child_resource ? 'active' : '' }}" id="{{ $child_resource }}">        
        {{ $child_inner_index }}
    </div>
    @endif
</div>