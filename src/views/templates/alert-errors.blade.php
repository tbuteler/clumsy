@if ($errors->count())

    <div id="main-alert" class="alert alert-warning alert-dismissable">
        <div class="container">
            <ul class="list-unstyled">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" data-target="#main-alert" aria-hidden="true">&times;</button>
        </div>
    </div>

@endif
