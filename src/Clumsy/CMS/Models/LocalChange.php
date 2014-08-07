<?php namespace Clumsy\CMS\Models;

class LocalChange extends \Eloquent {

    protected $guarded = array('id');

    public static function preserve($data, $resource_type)
    {
        $changes = LocalChange::where('resource_id', $data['id'])->where('resource_type', $resource_type)->get();
        
        foreach ($changes as $changed) {

            unset($data[$changed->field]);
        }

        return $data;
    }
}