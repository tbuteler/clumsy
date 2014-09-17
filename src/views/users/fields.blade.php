{{ Form::field('first_name', trans('clumsy::fields.first_name')) }}

{{ Form::field('last_name', trans('clumsy::fields.last_name')) }}

{{ Form::field('email', trans('clumsy::fields.email')) }}

@if ($edited_user_id == 'new' || $edited_user_id != $user->id)

    {{ Form::dropdown('group', trans('clumsy::fields.role'), $groups, $edited_user_group) }}

    @if ($edited_user_id == 'new')

    <h3 class="page-header">{{ trans('clumsy::fields.password') }}</h3>

        {{ Form::field('password', trans('clumsy::fields.password'), 'password') }}

        {{ Form::field('confirm_password', trans('clumsy::fields.confirm_password'), 'password') }}

    @endif

@endif

@if ($edited_user_id == $user->id)

<h3 class="page-header">{{ trans('clumsy::fields.current_role') }}</h3>
    <ul>
        <li>{{ $usergroup }}</li>
    </ul>

<h3 class="page-header">{{ trans('clumsy::fields.change_password') }}</h3>

    {{ Form::field('new_password', trans('clumsy::fields.new_password'), 'password') }}

    {{ Form::field('confirm_new_password', trans('clumsy::fields.confirm_new_password'), 'password') }}

@endif