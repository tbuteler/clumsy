@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.login')
@stop

@section('page-header')
    <h1 class="page-header">@yield('title')</h1>
@stop

@section('master')

    @form

        @field('email', trans('clumsy::fields.email'), 'type:email|tabindex:1')

        @password('password', trans('clumsy::fields.password'), ['tabindex' => 2, 'beforeLabel' => '<a tabindex="5" href="'.route('clumsy.reset-password').'" class="pull-right">'.trans('clumsy::fields.reset-password').'</a>'])

        @checkbox('remember', trans('clumsy::fields.remember'), 'tabindex:3')

        <button class="btn btn-primary submit-once" tabindex="4">@lang('clumsy::buttons.login')</button>

    @endform

@stop
