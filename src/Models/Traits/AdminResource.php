<?php

namespace Clumsy\CMS\Models\Traits;

use Illuminate\Support\Facades\DB;

trait AdminResource
{
    use Relatable, Paged, Sortable, Reorderable, Filterable, Toggable;

    public function resourceParameter()
    {
        return snake_case(class_basename($this));
    }

    public function resourceName()
    {
        return str_slug($this->resourceParameter());
    }

    public function rules()
    {
        return property_exists($this, 'rules') ? $this->rules : [];
    }

    // Allow public access to hasCast method
    public function isCasted($key)
    {
        return $this->hasCast($key);
    }

    // Allow public access to getCastType method
    public function castAs($key)
    {
        return $this->getCastType($key);
    }

    public function booleans()
    {
        return array_keys(array_filter($this->casts, function ($type) {
            return in_array($type, ['bool', 'boolean']);
        }));
    }

    public function scopeManaged($query, $parentId = null)
    {
        return $query->filtered($parentId)
                     ->orderSortable();
    }

    public function scopeGetManaged($query, $parentId = null)
    {
        return $query->managed($parentId)->getPaged();
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

        if (!$query->getQuery()->columns) {
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
