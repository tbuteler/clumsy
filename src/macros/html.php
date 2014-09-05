<?php

use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Clumsy\Utils\Facades\HTTP;

HTML::macro('columnTitle', function($resource, $column, $name)
{
    $url = URL::route('sort', $resource);
    $attributes = array();

    if (Session::has("clumsy.order.$resource"))
    {
        if ($column === head(Session::get("clumsy.order.$resource")))
        {
        	$direction = last(Session::get("clumsy.order.$resource"));

        	$attributes['class'] = "text-primary active $direction";
			
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
    }
    else
    {
		$url = HTTP::queryStringAdd($url, 'column', $column);
		$url = HTTP::queryStringAdd($url, 'direction', 'asc');
    }

	return HTML::link($url, $name, $attributes);
});