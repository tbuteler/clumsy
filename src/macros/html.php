<?php

use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Clumsy\Utils\Facades\HTTP;

HTML::macro('columnTitle', function($resource, $column, $name)
{
    $url = URL::route('sort', $resource);
    $attributes = array();
    $html = '';

    if (Session::has("clumsy.order.$resource"))
    {
        if ($column === head(Session::get("clumsy.order.$resource")))
        {
            $direction = last(Session::get("clumsy.order.$resource"));

            $attributes['class'] = "active $direction";
            $html = '<span class="caret"></span>';

            if ('desc' === $direction)
            {
                $url = HTTP::queryStringAdd($url, 'reset');
            }
            else
            {
                $url = HTTP::queryStringAdd($url, 'column', $column);
                $url = HTTP::queryStringAdd($url, 'direction', 'desc');
            }
        }
        else
        {
            $url = HTTP::queryStringAdd($url, 'column', $column);
            $url = HTTP::queryStringAdd($url, 'direction', 'asc');
        }
    }
    else
    {
        $url = HTTP::queryStringAdd($url, 'column', $column);
        $url = HTTP::queryStringAdd($url, 'direction', 'asc');
    }

    $html = HTML::link($url, $name, $attributes).$html;

    return $html;
});

HTML::macro('breadcrumb', function($breadcrumb)
{
    $last = array_pop($breadcrumb);
    $html = '<ol class="breadcrumb">';
    foreach ($breadcrumb as $crumb_link => $crumb)
    {
        $html .= '<li><a href="'.$crumb_link.'">'.$crumb.'</a></li>';
    }
    $html .= '<li class="active">'.$last.'</li>';
    $html .= '</ol>';

    return $html;
});