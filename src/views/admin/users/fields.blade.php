{{ Form::field('first_name', trans('clumsy/cms::fields.first_name')) }}

{{ Form::field('last_name', trans('clumsy/cms::fields.last_name')) }}

{{ Form::field('email', trans('clumsy/cms::fields.email')) }}

@if ($edited_user_id == 'new' || $edited_user_id != $user->id)

    {{ Form::dropdown('group', trans('clumsy/cms::fields.role'), array('Administrators' => trans('clumsy/cms::fields.roles.administrator'), 'Editors' => trans('clumsy/cms::fields.roles.editor')), $edited_user_group) }}

    @if ($edited_user_id == 'new')

    <h3 class="page-header">{{ trans('clumsy/cms::fields.password') }}</h3>

        {{ Form::field('password', trans('clumsy/cms::fields.password'), 'password') }}

        {{ Form::field('confirm_password', trans('clumsy/cms::fields.password'), 'password') }}

    @endif

@endif

@if ($edited_user_id == $user->id)

<h3 class="page-header">{{ trans('clumsy/cms::fields.current_role') }}</h3>
    <ul>
        <li>{{ $usergroup }}</li>
    </ul>

<h3 class="page-header">{{ trans('clumsy/cms::fields.change_password') }}</h3>

    {{ Form::field('new_password', trans('clumsy/cms::fields.new_password'), 'password') }}

    {{ Form::field('confirm_new_password', trans('clumsy/cms::fields.confirm_password'), 'password') }}

@endif