@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.login')
@stop

@section('master')

    {{ Form::open() }}

        {{ Form::hidden('intended', $intended ? $intended : route("$admin_prefix.home")) }}

        {{ Form::field('email', trans('clumsy::fields.email'), 'email', array('field' => array('tabindex' => '1'))) }}

        <div class="form-group">
            <a tabindex="5" href="{{ route('clumsy.reset-password') }}" class="pull-right">
                {{ trans('clumsy::fields.reset-password') }}
            </a>
            {{ Form::label('password', trans('clumsy::fields.password')) }}
            {{ Form::password('password', array('class' => 'form-control', 'tabindex' => '2')) }}
        </div>

        {{ Form::boolean('remember', trans('clumsy::fields.remember'), array('field' => array('tabindex' => '3'))) }}

        <button class="btn btn-primary submit-once" tabindex="4">@lang('clumsy::buttons.login')</button>

    {{ Form::close() }}

@stop