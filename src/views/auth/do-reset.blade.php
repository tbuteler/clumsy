@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.reset-password')
@stop

@section('master')

    {{ Form::open() }}

        {{ Form::field('password', trans('clumsy::fields.password'), 'password') }}

        {{ Form::field('password_confirmation', trans('clumsy::fields.password_confirmation'), 'password') }}

        <button class="btn btn-primary submit-once">@lang('clumsy::buttons.reset')</button>

    {{ Form::close() }}

@stop