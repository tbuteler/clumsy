<a class="navbar-brand" href="{{ url("$admin_prefix") }}">
@section('admin-title')
    {{ $admin_title or '<span class="visible-xs">Home</span><span class="glyphicon glyphicon-home hidden-xs"></span>' }}
@show
</a>