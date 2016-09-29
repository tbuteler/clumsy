<div class="col-sm-6 after-title">
@section('after-title')
    @if (!$suppressAddResource)
    <a href="{{ $addResourceUrl }}" class="btn btn-success add-new">
        {{ $addResourceLabel }}
    </a>
    @endif
@show
</div>
