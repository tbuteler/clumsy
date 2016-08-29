<?php

namespace Clumsy\CMS\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Toggable
{
    public function scopeOfType(Builder $query, $type)
    {
        return $query->where('type', $type);
    }
}
