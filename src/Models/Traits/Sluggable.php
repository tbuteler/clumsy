<?php

namespace Clumsy\CMS\Models\Traits;

trait Sluggable
{
    public static function bootSluggable()
    {
        self::saving(function ($model) {
            $model->slug = $model->getSlug();
        });
    }

    public function getSlug()
    {
        $makeSlugFrom = property_exists($this, 'makeSlugFrom') ? $this->makeSlugFrom : 'title';
        return str_slug($this->$makeSlugFrom);
    }
}