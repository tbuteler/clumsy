<nav class="navbar navbar-default" role="navigation">
  <div class="container">

    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">{{ trans('clumsy/cms::buttons.toggle_navbar') }}</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="{{ URL::to('admin') }}">{{ $admin_title or '' }}</a>
    </div>

    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

     {{ $navbar or '' }}
     
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" class="glyphicon glyphicon-user dropdown-toggle" data-toggle="dropdown"><b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li role="presentation" class="dropdown-header">{{ $usergroup }}</li>
            <li role="presentation"><a href="{{ URL::route('admin.user.edit', $user->id) }}">{{ $username }}</a></li>
            
            @if ($user->hasAccess('users'))
              <li role="presentation" class="divider"></li>
              <li role="presentation"><a href="{{ URL::route('admin.user.index') }}">Manage users</a></li>
            @endif

            <li role="presentation" class="divider"></li>
            <li><a href="{{ URL::route('logout') }}">Log out</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>