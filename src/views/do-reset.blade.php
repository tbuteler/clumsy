@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.reset-password')
@stop

@section('master')

    {{ Form::open(array('route' => 'post-do-reset-password')) }}

    {{ Form::hidden('user_id', $user_id) }}
    {{ Form::hidden('code', $code) }}

    {{ Form::field('password', trans('clumsy::fields.password'), 'password') }}

    {{ Form::field('password_confirmation', trans('clumsy::fields.confirm_password'), 'password') }}

    {{ Form::submit(trans('clumsy::buttons.reset'), array('class' => 'btn btn-primary')) }}

    {{ Form::close() }}

@stop