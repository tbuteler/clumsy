<?php

namespace Clumsy\CMS\Models\Traits;

use Carbon\Carbon;
use Hashids\Hashids;

trait Hashid
{
    public static function bootHashid()
    {
        self::creating(function ($model) {
            if ($model->hashIdBeforeCreating()) {
                $model->hash_id = $model->hashByTimestamp();
            } else {
                self::created(function ($model) {
                    $model->hash_id = $model->hashByPrimaryKey();
                    $model->save();
                });
            }
        });
    }

    public function hasher()
    {
        return new Hashids(config('app.key'), $this->minimumHashIdSize());
    }

    public function minimumHashIdSize()
    {
        return property_exists($this, 'minimumHashIdSize') ? $this->minimumHashIdSize : 4;
    }

    public function hashIdBeforeCreating()
    {
        return property_exists($this, 'hashIdBeforeCreating') && $this->hashIdBeforeCreating;
    }

    public function hashByTimestamp()
    {
        $datetime = Carbon::now();
        return $this->hasher()->encode(
            substr((string)$datetime->year, 1, 3),
            $datetime->month,
            $datetime->day,
            $datetime->hour,
            $datetime->minute,
            $datetime->second,
            round(head(explode(' ', microtime()))*1000),
            rand(0,999)
        );
    }

    public function hashByPrimaryKey()
    {
        return $this->hasher()->encode($this->getKey(), rand(0,999));
    }
}
