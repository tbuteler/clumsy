<?php

use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Clumsy\Utils\Facades\HTTP;
use Clumsy\CMS\Facades\International;

HTML::macro('columnTitle', function ($resource, $column, $name) {

    $url = route('clumsy.reorder', $resource);
    $attributes = array();
    $html = '';

    if (Session::has("clumsy.order.$resource")) {
        if ($column === head(Session::get("clumsy.order.$resource"))) {
        $direction = last(Session::get("clumsy.order.$resource"));

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

HTML::macro('booleanCell', function ($name, $checked, array $attributes = array()) {

    return Form::checkbox($name, 1, $checked, $attributes);
});

HTML::macro('booleanCaption', function ($name, $checked, array $attributes = array(), $label = null) {

    $attributes['field'] = $attributes;
    $attributes['class'] = 'form-group checkbox';
    $attributes['checked'] = $checked;

    return Form::boolean($name, $label, $attributes);
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

HTML::macro('translatable', function ($fields) {

    $locales = International::getSupportedLocales();

    reset($locales);
    $first = key($locales);

    return View::make('clumsy::macros.translatable', compact('locales', 'first', 'fields'))->render();
});
