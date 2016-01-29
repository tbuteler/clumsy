<?php

namespace Clumsy\CMS\Models\Traits;

trait ActiveOrNot
{
    public function scopeActive($query, $active = true)
    {
        return $query->where('active', $active);
    }
}
