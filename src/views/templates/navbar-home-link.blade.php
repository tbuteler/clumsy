<a class="navbar-brand" href="{{ url("$adminPrefix") }}">
@section('admin-title')
    {{ $admin_title or config('app.name') }}
@show
</a>
