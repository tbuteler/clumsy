<div class="row">

    <div class="col-md-{{ $item ? '8' : '12' }}">

        {{ Form::field('first_name', trans('clumsy::fields.first_name')) }}

        {{ Form::field('last_name', trans('clumsy::fields.last_name')) }}

        {{ Form::field('email', trans('clumsy::fields.email')) }}

        @if ($edited_user_id == 'new' || $edited_user_id != $user->id)

            {{ Form::dropdown('group', trans('clumsy::fields.role'), $groups, $edited_user_group) }}

            @if ($edited_user_id == 'new')

            <h3 class="page-header">{{ trans('clumsy::fields.password') }}</h3>

                {{ Form::field('password', trans('clumsy::fields.password'), 'password') }}

                {{ Form::field('password_confirmation', trans('clumsy::fields.password_confirmation'), 'password') }}

            @endif

        @endif

        @if ($edited_user_id == $user->id)

        <h3 class="page-header">{{ trans('clumsy::fields.current_role') }}</h3>
            <ul>
                <li>{{ $usergroup }}</li>
            </ul>

        <h3 class="page-header">{{ trans('clumsy::fields.change_password') }}</h3>

            {{ Form::field('new_password', trans('clumsy::fields.new_password'), 'password') }}

            {{ Form::field('new_password_confirmation', trans('clumsy::fields.new_password_confirmation'), 'password') }}

        @endif

    </div>

    @if ($item)
        <div class="col-md-4">

            <h4 class="page-header lead section-header">{{ trans('clumsy::titles.user_control') }}</h4>

            @if ($throttle->isEnabled())
                @if ($item_status->isBanned())
                    <p>{{ trans('clumsy::fields.user_is_banned') }}</p>
                @elseif ($item_status->isSuspended())
                    <p>{{ trans('clumsy::fields.user_is_suspended') }}</p>
                @elseif ($item->activated)
                    <p>{{ trans('clumsy::fields.user_is_active') }}</p>
                @else
                    <p>{{ trans('clumsy::fields.user_is_inactive') }}</p>
                @endif
            @endif

            <p>{{ trans('clumsy::fields.last_login') }}<strong>{{ $item->last_login ? display_date($user->last_login, 'long with time') : trans('clumsy::fields.never') }}</strong></p>

        </div>
    @endif

</div>