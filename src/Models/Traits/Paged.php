<?php

namespace Clumsy\CMS\Models\Traits;

trait Paged
{
    public function scopeGetPaged($query, $perPage = null)
    {
        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}