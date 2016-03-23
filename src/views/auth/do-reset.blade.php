@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.reset-password')
@stop

@section('master')

    @form

        @field('email', trans('clumsy::fields.email'), 'type:email')

        @password('password', trans('clumsy::fields.password'))

        @password('password_confirmation', trans('clumsy::fields.password_confirmation'))

        <button class="btn btn-primary submit-once">@lang('clumsy::buttons.reset')</button>

    @endform

@stop
