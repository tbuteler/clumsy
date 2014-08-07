@if (Session::has('message'))

    <div id="main-alert" class="alert alert-{{ Session::has('status') ? Session::get('status') : 'warning' }} alert-dismissable">
        <div class="container">
            <p>{{ Session::get('message') }}</p>
            <button type="button" class="close" data-dismiss="alert" data-target="#main-alert" aria-hidden="true">&times;</button>
        </div>
    </div>

@endif