<?php

namespace Clumsy\CMS\Panels\Traits;

trait Toggable
{
    protected $toggled;

    public function prepareToggable()
    {
        $this->setData([
            'toggleFilters' => $this->toggleFilters(),
            'indexType'     => $this->toggled,
        ]);

        if ($this->isUsingToggles()) {
            $this->setData('itemCount', $this->typeCounts());
        }

        if ($this->toggled) {
            $this->query->ofType($this->toggled);

            if (count($this->suppressWhenToggled())) {
                $columns = $this->getData('columns');
                foreach ($this->suppressWhenToggled() as $suppress) {
                    unset($columns[$suppress]);
                }
                $this->setData('columns', $columns);
            }

            if (count($this->appendWhenToggled())) {
                $columns = $this->getData('columns');
                foreach ($this->appendWhenToggled() as $append => $appendLabel) {
                    $columns[$append] = $appendLabel;
                }
                $this->setData('columns', $columns);
            }

        }
    }

    public function toggleFilters()
    {
        return property_exists($this, 'toggleFilters') ? $this->toggleFilters : [];
    }

    public function isUsingToggles()
    {
        return (bool)$this->toggleFilters();
    }

    public function suppressWhenToggled()
    {
        return property_exists($this, 'suppressWhenToggled') ? $this->suppressWhenToggled : [];
    }

    public function appendWhenToggled()
    {
        return property_exists($this, 'appendWhenToggled') ? $this->appendWhenToggled : [];
    }

    public function toggle($type)
    {
        $this->toggled = $type;
        $this->addContext('of_type', $type);
        $this->view->nestLevel($type)->pushLevel('index');
    }

    public function hasTypeCounter($type)
    {
        return method_exists($this, 'typeCount'.studly_case($type));
    }

    public function typeCount($type)
    {
        $method = 'typeCount'.studly_case($type);
        return $this->{$method}();
    }

    public function typeCounts()
    {
        $query = clone $this->query;

        $counts['all'] = $this->hasTypeCounter('all') ? $this->typeCount('all') : $query->count();

        foreach ($this->toggleFilters() as $filter => $filterLabel) {

            if ($filter === 'all') {
                continue;
            }

            if ($this->hasTypeCounter($filter)) {
                $counts[$filter] = $this->typeCount($filter);
            } else {
                $query = clone $this->query;
                $counts[$filter] = $query->ofType($filter)->count();
            }
        }

        return $counts;
    }
}
