<div class="row">

    <div class="col-md-16">

        <div class="row">
            <div class="col-md-12">
                @field('name', trans('clumsy::fields.name'))
            </div>
            <div class="col-md-12">
                @field('email', trans('clumsy::fields.email'))
            </div>
        </div>

        @if ($item->isGroupable())

            @if ($item->id != $user->id)
                @dropdown('group_ids', trans('clumsy::fields.role'), ['options' => $groups, 'selected' => $item->getGroupIds()])
            @else
                <h3 class="page-header">@lang('clumsy::fields.current_role')</h3>
                <ul>
                    <li>{{ $user->usergroup }}</li>
                </ul>
                <h3 class="page-header">@lang('clumsy::fields.change_password')</h3>
                <div class="row">
                    <div class="col-md-12">
                        @password('new_password', trans('clumsy::fields.new_password'))
                    </div>
                    <div class="col-md-12">
                        @password('new_password_confirmation', trans('clumsy::fields.new_password_confirmation'))
                    </div>
                </div>
            @endif

        @endif

    </div>

    <div class="col-md-8">

        <h4 class="page-header lead section-header">@lang('clumsy::titles.user_control')</h4>

        <p>{{ trans('clumsy::fields.last_login') }}<strong>{{ $item->last_login ? display_date($item->last_login, 'long with time') : trans('clumsy::fields.never') }}</strong></p>

    </div>

</div>
