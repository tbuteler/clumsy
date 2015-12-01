<?php

namespace Clumsy\CMS\Panels\Traits;

use Clumsy\CMS\Facades\International;

trait Translatable
{
    public function translatable($translatable)
    {
        $locales = International::getSupportedLocales();

        reset($locales);
        $first = key($locales);

        $data = array_merge(
            $this->getData(),
            compact('locales', 'first', 'translatable')
        );

        return view('clumsy::macros.translatable', $data)->render();
    }
}