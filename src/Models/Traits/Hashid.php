<?php

namespace Clumsy\CMS\Models\Traits;

use Hashids\Hashids;

trait Hashid
{
    public static function bootSluggable()
    {
        self::creating(function ($model) {
            $hasher = new Hashids(config('app.key'), 6);
            $model->hash_id = $hasher->encode($model->getKey());
        });
    }
}