@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.login')
@stop

@section('master')

    {{ Form::open() }}

    {{ Form::hidden('intended', $intended ? $intended : URL::route("$admin_prefix.home")) }}

    {{ Form::field('email', trans('clumsy::fields.email'), 'email', array('field' => array('tabindex' => '1'))) }}

    <div class="form-group">
        <a tabindex="5" href="{{ route('reset-password') }}" class="pull-right">@lang('clumsy::fields.reset-password')</a>
        {{ Form::label('password', trans('clumsy::fields.password')) }}
        {{ Form::password('password', array('class' => 'form-control', 'tabindex' => '2')) }}
    </div>

    {{ Form::boolean('remember', trans('clumsy::fields.remember'), array('field' => array('tabindex' => '3'))) }}

    {{ Form::submit(trans('clumsy::buttons.login'), array('class' => 'btn btn-primary', 'tabindex' => '4')) }}

    {{ Form::close() }}

@stop