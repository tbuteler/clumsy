<?php

namespace Clumsy\CMS\Models\Traits;

use Carbon\Carbon;
use Hashids\Hashids;

trait Hashid
{
    public static function bootHashid()
    {
        self::creating(function ($model) {
            $hasher = new Hashids(config('app.key'));
            $datetime = Carbon::now();
            $model->hash_id = $hasher->encode(
                substr((string)$datetime->year, 1, 3),
                $datetime->month,
                $datetime->day,
                $datetime->hour,
                $datetime->minute,
                $datetime->second,
                round(head(explode(' ', microtime()))*100),
                rand(0,9)
            );
        });
    }
}