<?php

namespace Clumsy\CMS\Models\Traits;

trait ActiveOrNot
{
    public function scopeActive($query, $active = true)
    {
        $table = $this->getTable();
        return $query->where("{$table}.active", $active);
    }
}
