<?php

namespace Clumsy\CMS\Models\Traits;

trait Toggable
{
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
