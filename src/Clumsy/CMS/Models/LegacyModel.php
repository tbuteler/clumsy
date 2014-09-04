<?php namespace Clumsy\CMS\Models;

use Clumsy\Eminem\Models\Media;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class LegacyModel extends \Eloquent {

    protected $guarded = array('id');

    public $required_by = array();

    public static $has_slug = false;
    
    public static $rules = array();
    public static $booleans = array();

    public static $default_order = array();

    public static $parent_resource = null;
    public static $parent_id_column = null;
    public static $child_resource = null;

    public static $media = array();

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
        
        self::creating(function($model)
        {
            if (isset($model->media_bind)) unset($model->media_bind);
        });
        
        self::created(function($model)
        {
            if (Input::has('media_bind'))
            {
                foreach (Input::get('media_bind') as $media_id => $attributes)
                {
                    $media = Media::find($media_id);

                    if ($media)
                    {
                        $options = array_merge(
                            array(
                                'association_id'   => $model->id,
                                'association_type' => class_basename($model),
                            ),
                            $attributes
                        );

                        $media->bind($options);
                    }
                }
            }
        });
    }

    public static function columns()
    {
        return Config::get('clumsy::default_columns');
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

    public function scopeOrderAuto($query)
    {
        $resource = snake_case(class_basename(get_class($this)));

        if (Session::has("clumsy.order.$resource"))
        {
            list($column, $direction) = Session::get("clumsy.order.$resource");
        }
        else
        {
            if (sizeof(static::$default_order) > 1)
            {
                list($column, $direction) = static::$default_order;
            }
            elseif (sizeof(static::$default_order) === 1)
            {
                $column = head(static::$default_order);
                $direction = 'asc';
            }
            else
            {
                $column = Config::get('clumsy::default_order.column');
                $direction = Config::get('clumsy::default_order.direction');
            }
        }

        return $query->orderBy($column, $direction);
    }

    public static function displayName()
    {
        return false;
    }

    public static function displayNamePlural()
    {
        return false;
    }

    public function images()
    {
        return $this->morphToMany('\Clumsy\Eminem\Models\Media', 'media_association')->select(array('media.*', 'position'));
    }

    public function hasImages()
    {
        return (bool)sizeof($this->images);
    }
    
    public function image($position = null, $offset = 0)
    {
        if ($this->hasImages())
        {
            if ($position)
            {
                $image = $this->images->filter(function($image) use ($position)
                    {
                        return $image->position === $position;
                    })
                    ->values();

                $image = $image->offsetExists($offset) ? $image->offsetGet($offset) : null;

            }
            else
            {    
                $image = $this->images->offsetExists($offset) ? $this->images->offsetGet($offset) : null;
            }

            if ($image)
            {
                return $image->path();
            }
        }

        return $this->imagePlaceholder($position);
    }

    public function imagePlaceholder($position = null)
    {
        return '';
    }

    public static function mediaSlots()
    {
        return static::$media;
    }
}