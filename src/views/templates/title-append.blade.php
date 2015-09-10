@if (isset($filterData) && $filterData)
    <i class="with-tooltip glyphicon glyphicon-filter title-btn {{ $filterData['hasFilters'] ? 'active' : '' }}" id="title-btn-filter" title="{{ trans('clumsy::titles.filters') }}" data-toggle="collapse" data-target="#filter-collapse"></i>
@endif
@if (isset($reorder) && $reorder)
    <a href="{{ $is_child ? $reorder_link : route("$admin_prefix.$resource.reorder") }}" class="with-tooltip glyphicon glyphicon-sort title-btn" id="title-btn-reorder" title="{{ trans('clumsy::titles.reorder', array('resources' => $model->displayNamePlural())) }}"></a>
@endif