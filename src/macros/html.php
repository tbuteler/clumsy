<?php

HTML::macro('mediaModal', function($id, $label, $media)
{
    if (!$media)
    {
        $media = new Illuminate\Support\Collection();
    }

    return View::make('admin.media.media-modal', compact('id', 'label', 'media'));
});