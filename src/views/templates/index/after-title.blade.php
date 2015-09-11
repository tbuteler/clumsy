<div class="col-sm-4 after-title">
@section('after-title')
    @if (isset($importer) && $importer)
        <a href="{{ route("$admin_prefix.$resource.import") }}" class="btn btn-primary add-new">
            {{ trans('clumsy::buttons.import', array('resources' => $model->displayNamePlural())) }}
        </a>
    @else
        <a href="{{ $create_link or route("$admin_prefix.$resource.create") }}" class="btn btn-success add-new">
            {{ trans('clumsy::buttons.add') }}
        </a>
    @endif
@show
</div>