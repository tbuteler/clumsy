<div class="col-sm-6 after-title" style="font-size:initial;">
@section('after-title')
    @include($view->resolve('export-button'))
    @if (!$suppressAddResource)
    <a href="{{ $addResourceUrl }}" class="btn btn-success add-new">
        {{ $addResourceLabel }}
    </a>
    @endif
@show
</div>
