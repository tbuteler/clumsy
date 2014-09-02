@if ($alert)

    <div id="main-alert" class="alert alert-{{ $alert_status }} alert-dismissable">
        <div class="container">
            <p>{{ $alert }}</p>
            <button type="button" class="close" data-dismiss="alert" data-target="#main-alert" aria-hidden="true">&times;</button>
        </div>
    </div>

@endif