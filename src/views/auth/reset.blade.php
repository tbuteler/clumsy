@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.reset-password')
@stop

@section('master')

    {{ Form::open() }}

        {{ Form::field('email', trans('clumsy::fields.email')) }}

        <button class="btn btn-primary submit-once">@lang('clumsy::buttons.reset')</button>

    {{ Form::close() }}

@stop