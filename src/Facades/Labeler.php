<?php

namespace Clumsy\CMS\Facades;

use Clumsy\CMS\Support\ResourceNameResolver;

class Labeler extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return new ResourceNameResolver;
    }
}
