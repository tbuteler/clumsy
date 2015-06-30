@if (isset($filtersData))
    <i class="glyphicon glyphicon-filter {{ $filtersData['hasFilters'] ? 'active' : '' }}" id="header-filter-btn" data-toggle="collapse" data-target="#filter-colapse"></i>
@endif
@if (isset($reorder) && $reorder)
    <a class="glyphicon glyphicon-sort" id="header-reorder-btn" href="{{ route("$admin_prefix.$resource.reorder") }}"></a>
@endif