<div class="location">

    {!! Field::make($address, trans('clumsy::fields.address'))->append('<button class="btn btn-primary geocoder-btn" type="button"><span class="glyphicon glyphicon-map-marker"></span></button>') !!}

    <div class="coordinates">

        <div class="row">
            <div class="col-md-6">
                @field($lat, trans('clumsy::fields.latitude'))
            </div>
            <div class="col-md-6">
                @field($lng, trans('clumsy::fields.longitude'))
            </div>
        </div>

        <small class="help-block">@lang('clumsy::fields.coordinates-help-block')</small>
        <div class="panel panel-default">
            <div class="map panel-body"></div>
        </div>
        <button type="button" class="drop-pin btn btn-sm btn-primary btn-block">@lang('clumsy::fields.drop-pin-at-center')</button>
    </div>

</div>
