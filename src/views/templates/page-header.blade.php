<h1 class="page-header">
    <div class="row">
        <div class="col-sm-18">
            @include($view->resolve('title-prepend'))
            @section('title')
                {{ $title or '' }}
            @show
            @include($view->resolve('title-append'))
        </div>
        @include($view->resolve('after-title'))
    </div>
</h1>
