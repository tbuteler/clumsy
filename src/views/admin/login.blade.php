@extends('clumsy/cms::admin.templates.master')

@section('master')

    {{ Form::open() }}

    {{ Form::hidden('intended', Session::has('intended') ? Session::get('intended') : URL::route('admin.home')) }}

    <div class="form-group">
        {{ Form::label('email', 'Email') }}
        {{ Form::text('email', Input::old('email'), array('class' => 'form-control')) }}
    </div>

    <div class="form-group">
        {{ Form::label('password', 'Password') }}
        {{ Form::password('password', array('class' => 'form-control')) }}
    </div>

    <div class="checkbox">
        <label for="remember">
            {{ Form::checkbox('remember', '1', Input::old('remember'), array('id' => 'remember')) }}
            Stay logged in
        </label>
    </div>

    {{ Form::submit('Login', array('class' => 'btn btn-primary')) }}

    {{ Form::close() }}

@stop