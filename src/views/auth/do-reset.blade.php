@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.reset-password')
@stop

@section('master')

    {!! Form::open() !!}

        {!! field('email', trans('clumsy::fields.email'))->type('email') !!}

        {!! field('password', trans('clumsy::fields.password'))->type('password') !!}

        {!! field('password_confirmation', trans('clumsy::fields.password_confirmation'))->type('password') !!}

        <button class="btn btn-primary submit-once">@lang('clumsy::buttons.reset')</button>

    {!! Form::close() !!}

@stop