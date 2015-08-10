@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.reset-password')
@stop

@section('master')

    {{ Form::open() }}

    {{ Form::field('password', trans('clumsy::fields.password'), 'password') }}

    {{ Form::field('password_confirmation', trans('clumsy::fields.password_confirmation'), 'password') }}

    {{ Form::submit(trans('clumsy::buttons.reset'), array('class' => 'btn btn-primary')) }}

    {{ Form::close() }}

@stop