<?php namespace Clumsy\CMS\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BaseModel extends \Eloquent {

    protected $guarded = array('id');

    public $required_by = array();

    public static $has_slug = false;
    
    public static $columns = null;
    public static $rules = array();
    public static $booleans = array();

    public static $parent_resource = null;
    public static $parent_id_column = null;
    public static $child_resource = null;

    public static function boot()
    {
        parent::boot();
        
        if (static::$has_slug)
        {
            self::saving(function($model)
            {
                $model->slug = Str::slug($model->title);
            });
        }
    }

    public static function isNested()
    {
        return (bool)static::parentResource();
    }

    public static function parentResource()
    {
        return (string)static::$parent_resource;
    }

    public static function parentModel()
    {
        return (string)studly_case(static::$parent_resource);
    }

    public static function parentIdColumn()
    {
        return static::$parent_id_column === null ? static::$parent_resource.'_id' : static::$parent_id_column;
    }

    public static function parentItemId($id)
    {
        $id_column = self::parentIdColumn();
        return self::find($id)->$id_column;
    }

    public static function parentItem($id)
    {
        $parent_model = static::parentModel();
        return $parent_model::find(static::parentItemId($id));
    }

    public static function hasChildren()
    {
        return (bool)static::childResource();
    }

    public static function childResource()
    {
        return (string)static::$child_resource;
    }

    public static function childModel()
    {
        return (string)studly_case(static::$child_resource);
    }

    public function requiredBy()
    {
        $required_by = new Collection;

        foreach ((array)$this->required_by as $relationship)
        {
            $required_by = $required_by->merge(
                $this->$relationship()->first() ? $this->$relationship()->first() : array()
            );
        }

        return $required_by;
    }

    public function isRequiredByOthers()
    {
        return !self::requiredBy()->isEmpty();
    }

    public static function displayName()
    {
        return false;
    }

    public static function displayNamePlural()
    {
        return false;
    }
}