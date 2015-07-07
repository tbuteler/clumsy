<?php

use Illuminate\Support\Facades\App;

if (!function_exists('isAdmin'))
{
    function isAdmin()
    {
        return (bool)App::offsetGet('clumsy.admin');
    }
}