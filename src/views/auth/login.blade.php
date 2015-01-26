@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.login')
@stop

@section('master')

    {{ Form::open() }}

    {{ Form::hidden('intended', $intended ? $intended : URL::route("$admin_prefix.home")) }}

    {{ Form::field('email', trans('clumsy::fields.email')) }}

    <div class="form-group">
        <a href="{{ route('reset-password') }}" class="pull-right">@lang('clumsy::fields.reset-password')</a>
        {{ Form::label('password', trans('clumsy::fields.password')) }}
        {{ Form::password('password', array('class' => 'form-control')) }}
    </div>

    {{ Form::boolean('remember', trans('clumsy::fields.remember')) }}

    {{ Form::submit(trans('clumsy::buttons.login'), array('class' => 'btn btn-primary')) }}

    {{ Form::close() }}

@stop