<?php namespace Clumsy\CMS\Contracts;

interface InternationalInterface {

	public function getSupportedLocales();

	public function getCurrentLocale();
}