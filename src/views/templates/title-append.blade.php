@if (isset($filterData) && $filterData)
    <i class="with-tooltip glyphicon glyphicon-filter title-btn {{ $filterData['hasFilters'] ? 'active' : '' }}" id="title-btn-filter" title="{{ trans('clumsy::titles.filters') }}" data-toggle="collapse" data-target="#filter-collapse"></i>
@endif

@if (isset($reorder) && $reorder)
    <a href="{{ $reorderUrl ?: route("$routePrefix.reorder") }}" class="with-tooltip glyphicon glyphicon-sort title-btn" id="title-btn-reorder" title="{{ trans('clumsy::titles.reorder', ['resources' => $model->displayNamePlural()]) }}"></a>
@endif