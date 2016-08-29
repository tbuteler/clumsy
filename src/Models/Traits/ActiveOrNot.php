<?php

namespace Clumsy\CMS\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ActiveOrNot
{
    public function scopeActive(Builder $query, $active = true)
    {
        $table = $this->getTable();
        return $query->where("{$table}.active", $active);
    }
}
