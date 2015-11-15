<?php

namespace Clumsy\CMS\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class LocalChange extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'clumsy_local_changes';

    protected $guarded = ['id'];

    public static function preserve($data, $resource_type)
    {
        $changes = LocalChange::where('resource_id', $data['id'])->where('resource_type', $resource_type)->get();

        foreach ($changes as $changed) {
            unset($data[$changed->field]);
        }

        return $data;
    }
}
