<?php

namespace Clumsy\CMS\Panels\Traits;

use UnexpectedValueException;
use Collective\Html\FormFacade as Form;
use Clumsy\CMS\Panels\Traits\Panel;
use Clumsy\CMS\Panels\Traits\Translatable;
use Clumsy\CMS\Panels\Traits\Location;
use Clumsy\Eminem\Facade as MediaManager;

trait Editable
{
    use Panel, Translatable, Location;

    protected $item;

    public function prepareEditable()
    {
        $this->setData([
            'title'          => trans('clumsy::titles.edit_item', ['resource' => $this->getLabel()]),
            'id'             => request()->route()->getParameter($this->resourceParameter()),
            'backLink'       => route("{$this->routePrefix}.index"),
            'showResource'   => request('show', request('reorder')),
            'suppressDelete' => $this->suppressDelete(),
            'fields'         => $this->fields(),
            'buttons'        => $this->buttons(),
        ]);
    }

    public function loadItems()
    {
        if (!$this->itemsLoaded()) {
            $item = $this->loadItemById();
        }

        foreach ($this->item->requiredBy() as $required) {
            if (!method_exists($this->item, $required)) {
                throw new UnexpectedValueException('The model\'s required resources must be defined by a dynamic property with queryable Eloquent relations');
            }
        }

        if ($this->model->isNested()) {
            $backLink = $this->persistResourceOn($this->getParentModelEditUrl());
            $fields = $this->getData('fields');
            array_push($fields, Form::hidden($this->model->parentIdColumn(), $this->getParentModelId()));
            $this->setData(compact('backLink', 'fields'));
        }

        $this->setData('media', MediaManager::slots($this->item, $this->item->id));
    }

    public function loadItemById($id = null)
    {
        $item = is_null($id) ? $this->model : $this->query->find($id);
        $this->setItem($item);
        $this->setData('item', $item);
    }

    public function setItem($item)
    {
        $this->item = $item;
        $this->itemsLoaded = true;

        return $this;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function suppressDelete()
    {
        return property_exists($this, 'suppressDelete') ? $this->suppressDelete : $this->isExternalResource();
    }

    public function isExternalResource()
    {
        return $this->model->importable;
    }

    public function fields()
    {
        return [];
    }

    public function buttons()
    {
        return [];
    }
}
