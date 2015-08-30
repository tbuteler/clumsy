@if (isset($filtersData))
    <i class="glyphicon glyphicon-filter title-btn {{ $filtersData['hasFilters'] ? 'active' : '' }}" id="title-btn-filter" data-toggle="collapse" data-target="#filter-collapse"></i>
@endif
@if (isset($reorder) && $reorder)
    <a class="glyphicon glyphicon-sort title-btn" id="title-btn-reorder" href="{{ route("$admin_prefix.$resource.reorder") }}"></a>
@endif