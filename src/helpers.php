<?php

if (!function_exists('ebr'))
{
    function ebr($string)
    {
    	$string = e(preg_replace('#<br\s*/?>#', "\n", $string));
 
    	return nl2br($string);
    }
}

if (!function_exists('locale'))
{
	function locale()
    {
		return app('config')->get('app.locale');
	}
}

if (!function_exists('n'))
{
	function n($number, $dec = 2, $thousands = true)
    {
        return number_format($number, $dec, ',', ($thousands ? '.' : ''));
	}
}

if (!function_exists('pc'))
{
	function pc($number, $dec = 2, $thousands = true)
    {
        return n((($eq)*100)) . '%';
	}
}

if (!function_exists('is_associative'))
{
    function is_associative($array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }
}

if (!function_exists('is_nested'))
{
    function is_nested($array)
    {
        return (bool)is_array($array) && is_array(current($array));
    }
}