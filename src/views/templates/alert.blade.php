@if ($alert)

    <div id="main-alert" class="alert alert-{{ key($alert) }} alert-dismissable">
        <div class="container">
            <p>{!! head($alert) !!}</p>
            <button type="button" class="close" data-dismiss="alert" data-target="#main-alert" aria-hidden="true">&times;</button>
        </div>
    </div>

@endif