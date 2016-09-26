<?php

namespace Clumsy\CMS\Panels;

use Clumsy\CMS\Panels\Traits\Index;
use Clumsy\CMS\Panels\Traits\Gallery as GalleryTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Gallery
{
    use Index, GalleryTrait;

    protected $action = 'index';

    public $thumbnailSlot = null;
}
