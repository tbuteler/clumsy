<?php

namespace Clumsy\CMS\Models\Traits;

trait Relatable
{
    public function isNested()
    {
        return (bool)$this->parentResource();
    }

    public function parentResource()
    {
        return property_exists($this, 'parentResource') ? $this->parentResource : null;
    }

    public function parentResourceName()
    {
        return $this->{$this->parentResource()}()->getRelated()->resourceName();
    }

    public function parentModel()
    {
        return $this->{$this->parentResource()}()->getRelated();
    }

    public function parentIdColumn()
    {
        $parentIdColumn = property_exists($this, 'parentIdColumn') ? $this->parentIdColumn : null;

        if (is_null($parentIdColumn)) {
            $parentIdColumn = snake_case($this->parentResource()).'_id';
        }

        return $parentIdColumn;
    }

    public function parentItemId($id = null)
    {
        $parentId = $this->parentIdColumn();
        return $id ? $this->find($id)->$parentId : $this->$parentId;
    }

    public function parentItem($id = null)
    {
        $parentModel = $this->parentModel();
        return $parentModel::find($this->parentItemId($id));
    }

    public function hasChildren()
    {
        return (bool)$this->childResources();
    }

    public function childResources()
    {
        return array_merge(
            property_exists($this, 'childResource') ? (array)$this->childResource : [],
            property_exists($this, 'childResources') ? (array)$this->childResources : []
        );
    }

    public function requiredBy()
    {
        $requiredBy = collect();

        if (property_exists($this, 'requiredBy')) {
            foreach ((array)$this->requiredBy as $relationship) {
                $requiredBy = $requiredBy->merge(
                    $this->$relationship()->first() ? $this->$relationship()->first() : []
                );
            }
        }

        return $requiredBy;
    }

    public function isRequiredByOthers()
    {
        return !$this->requiredBy()->isEmpty();
    }
}
