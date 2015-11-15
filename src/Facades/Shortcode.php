<?php

namespace Clumsy\CMS\Facades;

class Shortcode extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return '\Clumsy\CMS\Contracts\ShortcodeInterface';
    }
}
