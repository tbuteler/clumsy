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

        $fieldColumns = [];
        array_walk($translatable, function ($field) use (&$fieldColumns) {
            $fieldColumns[] = $field instanceof Clumsy\Utils\Library\Field ? $field->getName() : '';
        });

        $data = array_merge(
            $this->getData(),
            compact('locales', 'first', 'fieldColumns', 'translatable')
        );

        return view('clumsy::macros.translatable', $data)->render();
    }
}