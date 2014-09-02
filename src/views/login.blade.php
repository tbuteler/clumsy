@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.login')
@stop

@section('master')

    {{ Form::open() }}

    {{ Form::hidden('intended', $intended ? $intended : URL::route("$admin_prefix.home")) }}

    <div class="form-group">
        {{ Form::label('email', trans('clumsy::fields.email')) }}
        {{ Form::text('email', Input::old('email'), array('class' => 'form-control')) }}
    </div>

    <div class="form-group">
        {{ Form::label('password', trans('clumsy::fields.password')) }}
        {{ Form::password('password', array('class' => 'form-control')) }}
    </div>

    <div class="checkbox">
        <label for="remember">
            {{ Form::checkbox('remember', '1', Input::old('remember'), array('id' => 'remember')) }}
            {{ trans('clumsy::fields.remember') }}
        </label>
    </div>

    {{ Form::submit(trans('clumsy::buttons.login'), array('class' => 'btn btn-primary')) }}

    {{ Form::close() }}

@stop