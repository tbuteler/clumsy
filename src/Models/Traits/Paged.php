<?php

namespace Clumsy\CMS\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Paged
{
    public function scopeGetPaged(Builder $query, $perPage = null)
    {
        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
