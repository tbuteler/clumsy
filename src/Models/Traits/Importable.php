<?php

namespace Clumsy\CMS\Models\Traits;

use Clumsy\CMS\Models\LocalChange;

trait Importable
{
    public $importable = true;

    protected static function bootImportable()
    {
        self::updating(function ($item) {

            foreach ($item->getDirty() as $field => $value) {
                // Double check for changes by removing whitespace (HMTL content can be sneaky)
                if (preg_replace('/\s/', '', $item->getOriginal($field)) === preg_replace('/\s/', '', $item->$field)) {
                    continue;
                }

                LocalChange::firstOrCreate([
                    'resource_type' => $item->resource_name,
                    'resource_id'   => $item->id,
                    'field'         => $field,
                ]);
            }
        });
    }
}
