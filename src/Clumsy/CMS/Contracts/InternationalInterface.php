<?php
namespace Clumsy\CMS\Contracts;

interface InternationalInterface
{

    public function setLocale();

    public function getSupportedLocales();

    public function getCurrentLocale();

    public function localizeURL();

    public function getURLFromRouteNameTranslated();
}
