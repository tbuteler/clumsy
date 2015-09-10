@if (isset($filterData) && $filterData)
    <i class="glyphicon glyphicon-filter title-btn {{ $filterData['hasFilters'] ? 'active' : '' }}" id="title-btn-filter" data-toggle="collapse" data-target="#filter-collapse"></i>
@endif
@if (isset($reorder) && $reorder)
    <a class="glyphicon glyphicon-sort title-btn" id="title-btn-reorder" href="{{ $is_child ? $reorder_link : route("$admin_prefix.$resource.reorder") }}"></a>
@endif