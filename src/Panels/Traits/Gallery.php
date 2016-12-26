<?php

namespace Clumsy\CMS\Panels\Traits;

use Illuminate\Database\Eloquent\Model as Eloquent;

trait Gallery
{
    public function galleryColumnsPerRow()
    {
        $columnsPerRow = $this->getOptionalProperty('columnsPerRow', 4);

        if (!in_array($columnsPerRow, [1,2,3,4,6,12])) {
            return 4;
        }

        return $columnsPerRow;
    }

    public function thumbnailSlot()
    {
        return $this->getOptionalProperty('thumbnailSlot');
    }

    public function galleryThumbnail(Eloquent $item)
    {
        return '<img src="'.$item->firstMedia($this->thumbnailSlot()).'" class="img-responsive" alt="image">';
    }

    public function beforeRenderGallery()
    {
        $this->columnsPerRow = $this->galleryColumnsPerRow();

        $this->columnSize = 24/$this->columnsPerRow;
    }
}
