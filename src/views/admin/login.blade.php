@extends('clumsy/cms::admin.templates.master')

@section('master')

    {{ Form::open() }}

    {{ Form::hidden('intended', Session::has('intended') ? Session::get('intended') : URL::route('admin.home')) }}

    <div class="form-group">
        {{ Form::label('email', trans('clumsy/cms::fields.email')) }}
        {{ Form::text('email', Input::old('email'), array('class' => 'form-control')) }}
    </div>

    <div class="form-group">
        {{ Form::label('password', trans('clumsy/cms::fields.password')) }}
        {{ Form::password('password', array('class' => 'form-control')) }}
    </div>

    <div class="checkbox">
        <label for="remember">
            {{ Form::checkbox('remember', '1', Input::old('remember'), array('id' => 'remember')) }}
            {{ trans('clumsy/cms::fields.remember') }}
        </label>
    </div>

    {{ Form::submit(trans('clumsy/cms::buttons.login'), array('class' => 'btn btn-primary')) }}

    {{ Form::close() }}

@stop