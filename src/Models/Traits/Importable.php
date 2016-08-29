<?php

namespace Clumsy\CMS\Models\Traits;

use Clumsy\CMS\Models\LocalChange;
use Illuminate\Database\Eloquent\Model as Eloquent;

trait Importable
{
    public $importable = true;

    protected static function bootImportable()
    {
        self::updating(function (Eloquent $item) {

            foreach ($item->getDirty() as $field => $value) {
                // Double check for changes by removing whitespace (HMTL content can be sneaky)
                if (preg_replace('/\s/', '', $item->getOriginal($field)) === preg_replace('/\s/', '', $item->$field)) {
                    continue;
                }

                LocalChange::firstOrCreate([
                    'resource_type' => $item->resourceName(),
                    'resource_id'   => $item->id,
                    'field'         => $field,
                ]);
            }
        });
    }
}
