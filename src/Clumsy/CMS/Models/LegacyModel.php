<?php namespace Clumsy\CMS\Models;

use Clumsy\Eminem\Models\Media;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class LegacyModel extends \Eloquent {

    public static $has_slug = false;

    protected $guarded = array('id');

    public $resource_name;

    public $required_by = array();

    public $columns;
    public $column_equivalence = array();

    public $all_columns = array();

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
    public $filter_equivalence = array();

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

        self::saving(function($model)
        {
            if (isset($model->files)) unset($model->files);
        });

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

    public function columns()
    {
        return (array)($this->columns ? $this->columns : Config::get('clumsy::default_columns'));
    }

    public function columnEquivalence()
    {
        return (array)($this->column_equivalence);
    }

    public function allColumns()
    {
        return (array)($this->all_columns);
    }

    public function columnNames()
    {
        $columnNames = array();
        $equivalences = array_flip($this->columnEquivalence());
        $columns = $this->allColumns() + $this->columns();

        foreach (array_merge(array_keys($equivalences), array_keys($columns)) as $column)
        {
            $original = $column;
            
            // If we can't find the column name, look for an equivalence
            if (!isset($columns[$column]))
            {
                $column = $equivalences[$column];
            }

            // If we still can't find it, it could have been added dynamically, so ignore
            if (!isset($columns[$column]))
            {
                continue;
            }

            $columnNames[$original] = $columns[$column];
        }

        return $columnNames;
    }

    public function booleans()
    {
        return array_merge($this->booleans, $this->active_booleans);
    }

    public function filters()
    {
        return (array)$this->filters;
    }

    public function filterEquivalence()
    {
        return array_merge($this->columnEquivalence(), $this->filter_equivalence);
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
        return $this->find($id)->$id_column;
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
        return !$this->requiredBy()->isEmpty();
    }

    public function orderEquivalence()
    {
        return array_merge($this->columnEquivalence(), $this->order_equivalence);
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

    public function scopeCustomFilter($query)
    {
        if (Session::has("clumsy.filter.{$this->resource_name}"))
        {
            $buffer = Session::get("clumsy.filter.{$this->resource_name}");
            foreach ($buffer as $column => $values)
            {
                $equivalence = $this->filterEquivalence();
                $column = array_key_exists($column, $equivalence) ? array_pull($equivalence, $column) : $column;

                // If the column exists in the table, use it
                if (in_array($column, Schema::getColumnListing($this->getTable())))
                {
                    $query->where(function($query) use($values, $column)
                    {
                        $i = 0;
                        foreach ($values as $item)
                        {
                            $where = $i === 0 ? 'where' : 'orWhere';
                            $query->$where($column, $item);
                            $i++;
                        }
                    });
                }
                // Otherwise, assume it's a nested filter and
                // look for the column in the child model
                else
                {
                    list($model, $newColumn) = explode('.', $column);
                    $query->whereHas($model, function($query) use($newColumn, $values)
                    {
                        $query->where(function($query) use($values, $newColumn)
                        {
                            $i = 0;
                            foreach ($values as $item)
                            {
                                $where = $i === 0 ? 'where' : 'orWhere';
                                $query->$where($newColumn, $item);
                                $i++;
                            }
                        });
                    });
                }
            }
        }
    }

    public function getFilterData($query)
    {
        $data = array();
        
        foreach ($this->filters() as $column)
        {
            $values = array();

            $queryaux = clone $query;

            $index = $column;
            $filter_key = str_contains($column, '.') ? last(explode('.', $column)) : $column;

            $equivalence = $this->filterEquivalence();
            $column = array_key_exists($column, $equivalence) ? array_get($equivalence, $column) : $column;

            // If the column exists in the table, use it
            if(in_array($column, Schema::getColumnListing($this->getTable())))
            {
                $items = $queryaux->select($column)->distinct()->get();
            }
            // Otherwise, assume it's a nested filter and
            // look for the column in the child model
            else
            {
                list($model, $column) = explode('.', $column);
                $items = with(new $model)->select($column)->distinct()->get();
            }
            
            // If the column is a boolean, use 'yes' or 'no' values
            if (in_array($filter_key, (array)$this->booleans()) && !$this->hasGetMutator($filter_key))
            {
                $items->each(function($item) use($column, $filter_key, &$values)
                {
                    $values[$item->getAttributeFromArray($column)] = $item->$column == 1 ? trans('clumsy::fields.yes') : trans('clumsy::fields.no');
                });
            }
            // Otherwise, use the default attribute (will use get mutator, if available)
            else
            {
                $items->each(function($item) use($column, $filter_key, &$values)
                {
                    $values[$item->getAttributeFromArray($column)] = $item->$filter_key;
                });
            }

            $data[$index] = $values;
        }

        return $data;
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

    public function media()
    {
        return $this->morphToMany('\Clumsy\Eminem\Models\Media', 'media_association')->select(array('media.*', 'position'));
    }

    public function mediaSlots()
    {
        return $this->media_slots;
    }

    public function hasMedia()
    {
        return (bool)sizeof($this->media);
    }

    public function mediaPath($position = null, $offset = 0)
    {
        if ($this->hasMedia())
        {
            if ($position)
            {
                $media = $this->media->filter(function($media) use ($position)
                    {
                        return $media->position === $position;
                    })
                    ->values();

                $media = $media->offsetExists($offset) ? $media->offsetGet($offset) : null;
            }
            else
            {
                $media = $this->media->offsetExists($offset) ? $this->media->offsetGet($offset) : null;
            }

            if ($media)
            {
                return $media->path();
            }
        }

        return $this->mediaPlaceholder($position);
    }

    public function mediaPlaceholder($position = null)
    {
        return '';
    }
}