<?php

if (!function_exists('ebr')) {

    function ebr($string) {

    	$string = e(preg_replace('#<br\s*/?>#', "\n", $string));

    	return nl2br($string);
    }
}

if (!function_exists('locale')) {

	function locale() {

		return Config::get('app.locale');
	}
}

if (!function_exists('n')) {

	function n($number, $dec = 2, $thousands = true) {

        return number_format($number, $dec, ',', ($thousands ? '.' : ''));
	}
}

if (!function_exists('pc')) {

	function pc($number, $dec = 2, $thousands = true) {

        return n((($eq)*100)) . '%';
	}
}

if (!function_exists('mb_ucwords')) {

	function mb_ucwords($str) {

		return Library\Support\Helper::MultibyteUCWords($str);
	}
}

if (!function_exists('is_associative')) {

    function is_associative($array) {

        return Library\Support\Helper::isAssociative($array);
    }
}

if (!function_exists('parse_links')) {

    function parse_links($str) {

        return Library\Support\Helper::parseLinks($str);
    }
}

if (!function_exists('parse_tweet')) {

    function parse_tweet($str) {

        return Library\Support\Helper::parseTweet($str);
    }
}

if (!function_exists('__')) {

	function __($text, $domain = 'default') {

		return Library\I18n\Gettext::__($text, $domain);
	}
}

if (!function_exists('_x')) {

	function _x($text, $context, $domain = 'default') {

		return Library\I18n\Gettext::_x($text, $context, $domain);
	}
}

if (!function_exists('_n')) {

	function _n($single, $plural, $number, $domain = 'default') {

		return Library\I18n\Gettext::_n($single, $plural, $number, $domain);
	}
}

if (!function_exists('_nx')) {

	function _nx($single, $plural, $number, $context, $domain = 'default') {

		return Library\I18n\Gettext::_nx($single, $plural, $number, $context, $domain);
	}
}