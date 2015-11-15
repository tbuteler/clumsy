<?php

namespace Clumsy\CMS\Facades;

class International extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return '\Clumsy\CMS\Contracts\InternationalInterface';
    }
}
