<div class="row">
	<h3 class="page-header clearfix">
		{{ trans('clumsy::titles.locator') }}
	</h3>
	<div class="col-sm-4">
		{{ Form::field($lat, trans('clumsy::fields.latitude')) }}
		{{ Form::field($lng, trans('clumsy::fields.longitude')) }}
	</div>
	<div class="col-sm-8">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4>{{ trans('clumsy::titles.map') }}</h4>
			</div>
			<div id="map" class="panel-body">
			</div>
		</div>
	</div>
</div>