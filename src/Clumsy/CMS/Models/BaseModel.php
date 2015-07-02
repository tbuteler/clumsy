<?php namespace Clumsy\CMS\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Clumsy\CMS\Facades\Clumsy;

class BaseModel extends \Eloquent {

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
    public $child_resources = null;

    public $suppress_delete = false;

    public $media_slots = array();
    
    public $filters = array();
    public $filter_equivalence = array();

    public $toggle_filters = array();
    public $suppress_when_toggled = array();
    public $append_when_toggled = array();
    
    public $active_reorder = false;
    public $reorder_columns = array();

    public $innerViews = 'table';

    public $galleryThumbnailSlot = null;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->resource_name = snake_case(class_basename(get_class($this)));
    }

    protected static function boot()
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

    public function reorderViewColumns()
    {
        return count($this->reorder_columns) ? $this->reorder_columns : array_slice($this->columns, 0, 1);
    }

    public function columns()
    {
        $innerView = $this->getAdminContext('inner_view');
        $viewColumnsMethod = camel_case($innerView).'ViewColumns';
        if ($innerView && method_exists($this, $viewColumnsMethod))
        {
            $columns = $this->{$viewColumnsMethod}();
        }
        else
        {
            $columns = (array)($this->columns ? $this->columns : Config::get('clumsy::default_columns'));
        }

        $this->prepareColumns($columns);
        return $columns;
    }

    public function prepareColumns(&$columns) {}

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

    public function toggleFilters()
    {
        return (array)$this->toggle_filters;
    }

    public function suppressWhenToggled()
    {
        return (array)$this->suppress_when_toggled;
    }

    public function appendWhenToggled()
    {
        return (array)$this->append_when_toggled;
    }

    public function resourceToModel($resource)
    {
        return (string)studly_case($resource);
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
        return $this->resourceToModel($this->parent_resource);
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
        return (bool)$this->childResources();
    }

    public function childResources()
    {
        return array_merge((array)$this->child_resource, (array)$this->child_resources);
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

    public function hasSorter($column)
    {
        return method_exists($this, 'sort'.studly_case($column).'Column');
    }

    public function currentInnerView()
    {
        return Session::get("clumsy.inner-view.{$this->resource_name}", head((array)$this->innerViews));
    }

    public function scopeOrderSortable($query, $column = null, $direction = 'asc')
    {
        $sorted = false;

        if (Session::has("clumsy.order.{$this->resource_name}"))
        {
            list($column, $direction) = Session::get("clumsy.order.{$this->resource_name}");

            if ($this->hasSorter($column))
            {
                return $this->{'sort'.studly_case($column).'Column'}($query, $direction);
            }
        
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

    public function scopeGetPaged($query)
    {
        $per_page = property_exists($this, 'admin_per_page') ? $this->admin_per_page : Config::get('clumsy::per_page');

        return $per_page ? $query->paginate($per_page) : $query->get();
    }

    public function hasFilterer($column)
    {
        return method_exists($this, 'filter'.studly_case(str_replace('.', '_', $column)).'Column');
    }

    public function scopeFiltered($query)
    {
        if (Session::has("clumsy.filter.{$this->resource_name}"))
        {
            $buffer = Session::get("clumsy.filter.{$this->resource_name}");
            foreach ($buffer as $column => $values)
            {
                $equivalence = $this->filterEquivalence();
                $column = array_key_exists($column, $equivalence) ? array_get($equivalence, $column) : $column;

                if ($this->hasFilterer($column))
                {
                    return $this->{'filter'.studly_case(str_replace('.', '_', $column)).'Column'}($query, $values);
                }

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

    public function scopeManaged($query)
    {
        return $query->filtered()
                     ->orderSortable();
    }

    public function scopeGetManaged($query)
    {
        return $query->managed()->getPaged();
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getFilterData($query)
    {
        $data = array();
        
        foreach ($this->filters() as $column)
        {
            $values = array();

            $queryaux = clone $query;
            
            // Remove eager loads from query
            $queryaux->setEagerLoads(array());

            $filter_key = str_contains($column, '.') ? last(explode('.', $column)) : $column;

            $equivalence = $this->filterEquivalence();
            $column = array_key_exists($column, $equivalence) ? array_get($equivalence, $column) : $column;

            $index = $column;

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
                    $attributes = $item->getAttributes();
                    $values[$attributes[$column]] = $item->$column == 1 ? trans('clumsy::fields.yes') : trans('clumsy::fields.no');
                });
            }
            // Otherwise, use the default attribute (will use get mutator, if available)
            else
            {
                $items->each(function($item) use($column, $filter_key, &$values)
                {
                    $attributes = $item->getAttributes();
                    $values[$attributes[$column]] = $item->$filter_key;
                });
            }

            asort($values);

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

        $url = URL::route(Clumsy::prefix().".{$this->resource_name}.edit", $this->id);

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

    public function galleryThumbnail()
    {
        return '<img src="'.$this->mediaPath($this->galleryThumbnailSlot).'" class="img-responsive" alt="image">';
    }

    public function adminContextPrefix()
    {
        return 'clumsy_';
    }

    public function setAdminContext($context, $value)
    {
        $context = $this->adminContextPrefix().$context;
        $this->{$context} = $value;

        return $this;
    }

    public function getAdminContext($context)
    {
        $context = $this->adminContextPrefix().$context;
        return $this->{$context};
    }

    public function scopeWithAdminContext($query, $context, $value)
    {
        $context = snake_case($this->adminContextPrefix().$context);
        $value = DB::connection()->getPdo()->quote($value);

        if (!$query->getQuery()->columns)
        {
            $query->select('*');
        }

        $query->addSelect(DB::raw("$value as `$context`"));
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