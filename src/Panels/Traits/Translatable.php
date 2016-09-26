<?php

namespace Clumsy\CMS\Panels\Traits;

use Clumsy\CMS\Facades\International;
use Clumsy\Utils\Library\Field;

trait Translatable
{
    public function translatable($translatable)
    {
        $locales = International::getSupportedLocales();

        reset($locales);
        $first = key($locales);

        $fieldColumns = [];
        array_walk($translatable, function ($field) use (&$fieldColumns) {
            $fieldColumns[] = $field instanceof Field ? $field->getName() : '';
        });

        $data = array_merge(
            $this->getData(),
            compact('locales', 'first', 'fieldColumns', 'translatable')
        );

        return view($this->view->resolve('macros.translatable'), $data)->render();
    }

    public function translatedMedia($slot)
    {
        return view($this->view->resolve('macros.translated-media'), array_merge($this->getData(), ['baseSlot' => $slot]));
    }
}
