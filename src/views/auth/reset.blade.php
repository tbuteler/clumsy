@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.reset-password')
@stop

@section('inner-title')
    @lang('clumsy::titles.reset-password')
@stop

@section('master')

    {{ Form::open() }}

    {{ Form::field('email', trans('clumsy::fields.email')) }}

    {{ Form::submit(trans('clumsy::buttons.reset'), array('class' => 'btn btn-primary')) }}

    {{ Form::close() }}

@stop