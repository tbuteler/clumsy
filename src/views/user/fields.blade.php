<div class="row">

    <div class="col-md-{{ $item->exists ? '8' : '12' }}">

        <div class="row">
            <div class="col-md-6">
                @field('name', trans('clumsy::fields.name'))
            </div>
            <div class="col-md-6">
                @field('email', trans('clumsy::fields.email'))
            </div>
        </div>

        @if ($item->isGroupable() && $item->exists && $item->id != $user->id)
            @dropdown('group_ids', trans('clumsy::fields.role'), ['options' => $groups, 'selected' => $item->getGroupIds()])
        @endif

        @if (!$item->exists)

            @if ($item->isGroupable())
                @dropdown('group_ids', trans('clumsy::fields.role'), ['options' => $groups])
            @endif

            <h3 class="page-header">@lang('clumsy::fields.password')</h3>

            <div class="row">
                <div class="col-md-6">
                    @field('password', trans('clumsy::fields.password'), 'type:password')
                </div>
                <div class="col-md-6">
                    @field('password_confirmation', trans('clumsy::fields.password_confirmation'), 'type:password')
                </div>
            </div>


        @elseif ($item->isGroupable() &&$item->id == $user->id)

        <h3 class="page-header">@lang('clumsy::fields.current_role')</h3>
            <ul>
                <li>{{ $user->usergroup }}</li>
            </ul>

        <h3 class="page-header">@lang('clumsy::fields.change_password')</h3>

            <div class="row">
                <div class="col-md-6">
                    @password('new_password', trans('clumsy::fields.new_password'))
                </div>
                <div class="col-md-6">
                    @password('new_password_confirmation', trans('clumsy::fields.new_password_confirmation'))
                </div>
            </div>

        @endif

    </div>

    @if ($item->exists)

        <div class="col-md-4">

            <h4 class="page-header lead section-header">@lang('clumsy::titles.user_control')</h4>

            <p>{{ trans('clumsy::fields.last_login') }}<strong>{{ $item->last_login ? display_date($item->last_login, 'long with time') : trans('clumsy::fields.never') }}</strong></p>

        </div>
    @else

        @hidden('last_login', Carbon\Carbon::now())

    @endif

</div>
