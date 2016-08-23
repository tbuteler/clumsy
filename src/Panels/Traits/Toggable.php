<?php

namespace Clumsy\CMS\Panels\Traits;

trait Toggable
{
    protected $toggled;

    public function prepareToggable()
    {
        $this->setData([
            'toggleFilters' => $this->toggleFilters(),
            'indexType'     => $this->toggled ?: 'all',
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
        $toggles = $this->getOptionalProperty('toggleFilters', []);

        if (count($toggles) && !$this->suppressToggleAll()) {
            $toggles = array_prepend($toggles, array_get($toggles, 'all', trans('clumsy::buttons.all-resources')), 'all');
        }

        return $toggles;
    }

    public function toggleUrl($index)
    {
        if ($this->isChild()) {
            $baseUrl = $this->persistResourceOn($this->getParentModelEditUrl());

            if ($index === 'all') {
                return $baseUrl;
            }

            return app('clumsy.http')->queryStringAdd($baseUrl, $this->resourceName().'-type', $index);
        }

        if ($index === 'all') {
            return route("{$this->routePrefix}.index");
        }

        return route("{$this->routePrefix}.index-of-type", $index);
    }

    public function isUsingToggles()
    {
        return (bool) count($this->toggleFilters());
    }

    public function suppressWhenToggled()
    {
        return $this->getOptionalProperty('suppressWhenToggled', []);
    }

    public function appendWhenToggled()
    {
        return $this->getOptionalProperty('appendWhenToggled', []);
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

    public function suppressToggleAll()
    {
        return $this->getOptionalProperty('suppressToggleAll', false);
    }
}
