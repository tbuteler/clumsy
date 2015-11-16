<?php

if (!function_exists('isAdmin')) {

    function isAdmin()
    {
        return (bool) (app()->offsetGet('clumsy.admin'));
    }
}
