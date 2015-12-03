<?php

namespace Clumsy\CMS\Panels\Traits;

use Clumsy\Assets\Facade as Asset;

trait Location
{
    public function location($lat, $lng, $address = null)
    {
        Asset::enqueue('google-maps-admin');

        return view('clumsy::macros.location', compact('lat', 'lng', 'address'))->render();
    }
}