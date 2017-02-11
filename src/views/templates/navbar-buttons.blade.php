@section('navbar-buttons')
    <ul class="nav navbar-nav navbar-right">
        <li>
            @form(['route' => 'clumsy.logout', 'class' => 'form-logout'])
                <button href="{{ route('clumsy.logout') }}" type="submit" class="with-tooltip material-icons" title="{{ trans('clumsy::buttons.logout') }}" data-placement="bottom">&#xE879;</button>
            @endform
        </li>
    </ul>

    @if (Overseer::canManageUsers())
    <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
            <a href="#" class="material-icons with-tooltip dropdown-toggle hidden-xs" data-toggle="dropdown" title="{{ trans('clumsy::buttons.manage_site') }}" data-placement="bottom">&#xE8B8;</a>
            <a href="#" class="dropdown-toggle visible-xs" data-toggle="dropdown">
                {{ trans('clumsy::buttons.manage_site') }} <b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
                <li role="presentation">
                    <a href="{{ route("$userRoutePrefix.index") }}">{{ trans('clumsy::buttons.manage_users') }}</a>
                </li>
            </ul>
        </li>
    </ul>
    @endif

    <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
            <a href="#" class="material-icons with-tooltip dropdown-toggle hidden-xs" data-toggle="dropdown" title="{{ trans('clumsy::buttons.session') }}" data-placement="bottom">&#xE7FD;</a>
            <a href="#" class="dropdown-toggle visible-xs" data-toggle="dropdown">
            {{ trans('clumsy::buttons.session') }} <b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
                @if ($user->isGroupable())
                    <li role="presentation" class="dropdown-header">{{ $user->usergroup }}</li>
                @endif
                <li role="presentation">
                    <a href="{{ route("$userRoutePrefix.edit", $user->id) }}">{{ $user->username }}</a>
                </li>
            </ul>
        </li>
    </ul>
@show
