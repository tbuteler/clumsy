@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.login')
@stop

@section('alert')
    @include('clumsy::templates.alert')
    @include('clumsy::templates.alert-errors')
@stop

@section('master')

    {!! Form::open() !!}

        {!! Form::hidden('intended', $intended ? $intended : route("$adminPrefix.home")) !!}

        {!! field('email', trans('clumsy::fields.email'))->type('email')->tabindex(1) !!}

        {!! field('password', trans('clumsy::fields.password'))->type('password')->tabindex(2)->beforeLabel('<a tabindex="5" href="'.route('clumsy.reset-password').'" class="pull-right">'.trans('clumsy::fields.reset-password').'</a>') !!}

        {!! checkbox('remember', trans('clumsy::fields.remember'))->tabindex(3) !!}

        <button class="btn btn-primary submit-once" tabindex="4">@lang('clumsy::buttons.login')</button>

    {!! Form::close() !!}

@stop