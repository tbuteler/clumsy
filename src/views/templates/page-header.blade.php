<h1 class="page-header">
    <div class="row">
        <div class="col-sm-9">
            @section('title')
                {{ $title or '' }}
            @show
            @include($view->resolve('title-buttons'))
        </div>
        @include($view->resolve('after-title'))
    </div>
</h1>