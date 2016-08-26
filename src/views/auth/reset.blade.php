@extends('clumsy::templates.master')

@section('title')
    @lang('clumsy::titles.reset-password')
@stop

@section('page-header')
    <h1 class="page-header">@yield('title')</h1>
@stop

@section('master')

    @form

        {!! Field::make('email', trans('clumsy::fields.email'))->type('email')->tabindex(1)->beforeLabel('<a tabindex="3" href="'.route('clumsy.login').'" class="pull-right">'.trans('clumsy::fields.back-to-login').'</a>') !!}

        <button class="btn btn-primary submit-once" tabindex="2">@lang('clumsy::buttons.reset')</button>

    @endform

@stop
