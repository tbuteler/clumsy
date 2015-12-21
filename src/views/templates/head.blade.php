<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">

        <title>
        @section('title')
            {{ $title or '' }}
        @show
        </title>

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

    </head>
    <body class="{{ $bodyClass or '' }}">

    @section('alert')
        @include('clumsy::templates.alert')
    @show

    @if (Clumsy\CMS\Facades\Overseer::check())

        @include($navbarWrapper)

    @endif
