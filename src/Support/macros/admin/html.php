<?php

use Clumsy\CMS\Facades\Clumsy;
use Clumsy\CMS\Facades\International;
use Clumsy\Utils\Facades\HTTP;

HTML::macro('columnTitle', function ($resource, $column, $name) {

    $routePrefix = $resource;
    if ($prefix = Clumsy::prefix()) {
        $routePrefix = $prefix.'.'.$routePrefix;
    }
    $url = route("$routePrefix.sort");
    $attributes = [];
    $html = '';

    if (session()->has("clumsy.order.$resource")) {
        if ($column === head(session("clumsy.order.$resource"))) {
        $direction = last(session("clumsy.order.$resource"));

            $attributes['class'] = "active $direction";
            $html = '<span class="caret"></span>';

            if ('desc' === $direction) {
                $url = HTTP::queryStringAdd($url, 'reset');
            } else {
                $url = HTTP::queryStringAdd($url, 'column', $column);
                $url = HTTP::queryStringAdd($url, 'direction', 'desc');
            }
        } else {
            $url = HTTP::queryStringAdd($url, 'column', $column);
            $url = HTTP::queryStringAdd($url, 'direction', 'asc');
        }
    } else {
        $url = HTTP::queryStringAdd($url, 'column', $column);
        $url = HTTP::queryStringAdd($url, 'direction', 'asc');
    }

    $html = HTML::link($url, $name, $attributes).$html;

    return $html;
});

HTML::macro('booleanCell', function ($name, $checked, array $attributes = []) {

    return checkbox($name, null, $attributes)
            ->checked($checked)
            ->setGroupClass(null)
            ->noLabel();
});

HTML::macro('booleanCaption', function ($name, $checked, array $attributes = [], $label = null) {

    return checkbox($name, $label, $attributes)->checked($checked);
});

HTML::macro('breadcrumb', function ($breadcrumb) {

    $last = key(array_slice($breadcrumb, -1, 1));
    array_pop($breadcrumb);
    $html = '<ol class="breadcrumb">';
    foreach ($breadcrumb as $crumb => $crumb_link) {
        $html .= '<li><a href="'.$crumb_link.'">'.$crumb.'</a></li>';
    }
    $html .= '<li class="active">'.$last.'</li>';
    $html .= '</ol>';

    return $html;
});
