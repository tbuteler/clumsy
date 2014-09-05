<?php

use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Clumsy\Utils\Facades\HTTP;

if (!function_exists('sort_link'))
{
    function sort_link($resource, $column, $name)
    {
        if (Session::has("clumsy.order.$resource"))
        {
            list($column, $direction) = Session::get("clumsy.order.$resource");
        }
        
        $url = URL::route('sort', $resource);
        $url = HTTP::queryStringAdd($url, 'reset');

        return HTML::link($url, $name);
    }
}