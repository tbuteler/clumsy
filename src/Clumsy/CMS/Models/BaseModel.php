<?php namespace Clumsy\CMS\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class BaseModel extends \Eloquent {

    public static $has_slug = false;

    protected $guarded = array('id');

    public $resource_name;

    public $required_by = array();
    
    public $columns;
    public $rules = array();
    public $booleans = array();
    public $active_booleans = array();

    public $default_order = array();
    public $order_equivalence = array();

    public $parent_resource = null;
    public $parent_id_column = null;
    public $child_resource = null;

    public $suppress_delete = false;

    public $media_slots = array();
    
    public $filters = array();

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->resource_name = snake_case(class_basename(get_class($this)));
    }

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

    public function columns()
    {
        return $this->columns ? $this->columns : Config::get('clumsy::default_columns');
    }

    public function booleans()
    {
        return $this->booleans + $this->active_booleans;
    }

    public function filters()
    {
        return $this->filters;
    }

    public function isNested()
    {
        return (bool)$this->parentResource();
    }

    public function parentResource()
    {
        return (string)$this->parent_resource;
    }

    public function parentModel()
    {
        return (string)studly_case($this->parent_resource);
    }

    public function parentIdColumn()
    {
        return $this->parent_id_column === null ? $this->parent_resource.'_id' : $this->parent_id_column;
    }

    public function parentItemId($id)
    {
        $id_column = $this->parentIdColumn();
        return self::find($id)->$id_column;
    }

    public function parentItem($id)
    {
        $parent_model = $this->parentModel();
        return $parent_model::find($this->parentItemId($id));
    }

    public function hasChildren()
    {
        return (bool)$this->childResource();
    }

    public function childResource()
    {
        return (string)$this->child_resource;
    }

    public function childModel()
    {
        return (string)studly_case($this->child_resource);
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

    public function orderEquivalence()
    {
        return $this->order_equivalence;
    }

    public function scopeOrderSortable($query, $column = null, $direction = 'asc')
    {
        $sorted = false;

        if (Session::has("clumsy.order.{$this->resource_name}"))
        {
            list($column, $direction) = Session::get("clumsy.order.{$this->resource_name}");
        
            if (!in_array($column, Schema::getColumnListing($this->getTable())))
            {
                Session::forget("clumsy.order.{$this->resource_name}");
            }
            else
            {
                $sorted = true;
            }
        }
        elseif ($column)
        {
            $sorted = true;
        }
        
        if (!$sorted)
        {
            if (sizeof($this->default_order))
            {
                foreach ($this->default_order as $column => $direction)
                {
                    // If current row is not associative, rebuild variables
                    if (is_numeric($column))
                    {
                        $column = $direction;
                        $direction = 'asc';
                    }
                    
                    $query->orderBy($column, $direction);
                }

                return $query;
            }
            else
            {
                $column = Config::get('clumsy::default_order.column');
                $direction = Config::get('clumsy::default_order.direction');
            }
        }

        if ($column && $direction)
        {
            return $query->orderBy($column, $direction);
        }
        
        return $query;
    }

    public function rowClass()
    {
        return '';
    }

    public function cellClass($column)
    {
        $classes[] = 'cell-'.$column;

        if (in_array($column, (array)$this->active_booleans))
        {
            $classes[] = 'cell-active-boolean';
        }

        return implode(' ', $classes);
    }

    public function columnValue($column)
    {
        $value = $this->$column;
        
        if (!$this->hasGetMutator($column))
        {
            if (in_array($column, (array)$this->active_booleans))
            {
                return $this->activeBooleanColumnValue($column);
            }

            if (in_array($column, (array)$this->booleans))
            {
                return $this->booleanColumnValue($column);
            }
        }

        if ($value === false || $value === null) $value = $this->columnValuePlaceHolder();

        $url = URL::route(Config::get('clumsy::admin_prefix').".{$this->resource_name}.edit", $this->id);

        return HTML::link($url, $value);
    }

    public function columnValuePlaceHolder()
    {
        return '&nbsp;';
    }

    public function activeBooleanColumnValue($column)
    {
        return HTML::booleanCell($column, $this->$column, array(
            'id'          => 'ab-'.$this->id,
            'class'       => 'active-boolean',
            'data-id'     => $this->id,
            'data-column' => $column,
        ));
    }

    public function booleanColumnValue($column)
    {
        return $this->$column == 1 ? trans('clumsy::fields.yes') : trans('clumsy::fields.no');
    }

    /**
     * Return a timestamp as DateTime object
     * Doesn't output time when juggled as string, so can be used for date-only mutators
     *
     * @param  mixed  $value
     * @return \Carbon\Carbon
     */
    protected function asDate($value)
    {
        $value = parent::asDateTime($value);

        $value->setToStringFormat('Y-m-d');

        return $value;
    }

    public function displayName()
    {
        return false;
    }

    public function displayNamePlural()
    {
        return false;
    }
}