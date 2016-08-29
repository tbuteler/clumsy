<?php

namespace Clumsy\CMS\Panels;

use Clumsy\CMS\Panels\Traits\Index;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Gallery
{
    use Index;

    protected $action = 'index';

    public $thumbnailSlot = null;

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

    public function beforeRender()
    {
        $this->columnsPerRow = $this->galleryColumnsPerRow();

        $this->columnSize = 12/$this->columnsPerRow;
    }
}
