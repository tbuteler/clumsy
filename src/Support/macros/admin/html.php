<?php

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Clumsy\CMS\Facades\Clumsy;
use Clumsy\CMS\Facades\International;
use Clumsy\Utils\Facades\HTTP;

HTML::macro('columnTitle', function ($resource, $column, $name) {

    $prefix = Clumsy::prefix();
    $url = route("$prefix.$resource.sort");
    $attributes = [];
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

HTML::macro('translatable', function ($fields) {

    $locales = International::getSupportedLocales();

    reset($locales);
    $first = key($locales);

    return View::make('clumsy::macros.translatable', compact('locales', 'first', 'fields'))->render();
});
