@if (isset($filterData) && $filterData)
    <i class="with-tooltip material-icons title-btn {{ $filterData['hasFilters'] ? 'active' : '' }}" id="title-btn-filter" title="{{ trans('clumsy::titles.filters') }}" data-toggle="collapse" data-target="#filter-collapse">&#xE152;</i>
@endif

@if (isset($reorder) && $reorder)
    <a href="{{ $reorderUrl ?: route("$routePrefix.reorder") }}" class="with-tooltip material-icons title-btn" id="title-btn-reorder" title="{{ trans('clumsy::titles.reorder', ['resources' => $model->displayNamePlural()]) }}">&#xE8D5;</a>
@endif
