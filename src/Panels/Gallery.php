<?php

namespace Clumsy\CMS\Panels;

use Clumsy\CMS\Panels\Traits\Index;

class Gallery
{
    use Index;

    protected $action = 'index';

    public $thumbnailSlot = null;

    public function galleryColumnsPerRow()
    {
        $columnsPerRow = property_exists($this, 'columnsPerRow') ? $this->columnsPerRow : 4;

        if (!in_array($columnsPerRow, [1,2,3,4,6,12])) {
            return 4;
        }

        return $columnsPerRow;
    }

    public function thumbnailSlot()
    {
        return property_exists($this, 'thumbnailSlot') ? $this->thumbnailSlot : null;
    }

    public function galleryThumbnail($item)
    {
        return '<img src="'.$item->mediaPath($this->thumbnailSlot()).'" class="img-responsive" alt="image">';
    }

    public function beforeRender()
    {
        $this->columnsPerRow = $this->galleryColumnsPerRow();

        $this->columnSize = 12/$this->columnsPerRow;
    }
}
