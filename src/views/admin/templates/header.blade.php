<!doctype>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        
        <title>{{ $title or '' }}</title>

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

    </head>
    <body class="{{ $body_class or '' }}">

    @include('clumsy/cms::admin.templates.alert')
    
    @if (Cartalyst\Sentry\Facades\Laravel\Sentry::check())

        @include('clumsy/cms::admin.templates.navbar')

    @endif