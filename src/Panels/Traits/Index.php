<?php

namespace Clumsy\CMS\Panels\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Collective\Html\HtmlFacade as HTML;
use Clumsy\CMS\Panels\Traits\Panel;
use Clumsy\CMS\Panels\Traits\Sortable;
use Clumsy\CMS\Panels\Traits\Reorderable;
use Clumsy\CMS\Panels\Traits\Filterable;
use Clumsy\CMS\Panels\Traits\Toggable;
use Clumsy\CMS\Facades\Clumsy;
use Clumsy\Utils\Facades\HTTP;

trait Index
{
    use Panel, Sortable, Reorderable, Filterable, Toggable;

    protected $items;

    public function prepareIndex()
    {
        $updateUrl = route("{$this->routePrefix}.update");
        $updateUrl = str_replace(urlencode('{'.$this->model->resourceParameter().'}'), '{id}', $updateUrl);

        if ($this->model->importable) {
            $addResourceUrl = route("{$this->routePrefix}.import");
            $addResourceLabel = trans('clumsy::buttons.import', ['resources' => $this->getLabelPlural()]);
        } else {
            $addResourceUrl = route("{$this->routePrefix}.create");
            $addResourceLabel = trans('clumsy::buttons.add');
        }

        if ($this->isChild()) {
            $addResourceUrl = HTTP::queryStringAdd($addResourceUrl, 'parent', $this->getParentModelId());
        }

        $this->setData([
            'title'            => $this->getLabelPlural(),
            'updateUrl'        => $updateUrl,
            'addResourceUrl'   => $addResourceUrl,
            'addResourceLabel' => $addResourceLabel,
        ]);
    }

    public function loadItems()
    {
        if (!$this->itemsLoaded()) {
            $items = $this->query->getPaged($this->itemsPerPage());
            $this->setItems($items);
            $this->setData('items', $items);
        }

        $pagination = $items instanceof LengthAwarePaginator ? $items : null;
        if (!is_null($pagination)) {
            if($this->isChild()) {
                $pagination = $pagination->appends(['show' => $this->resourceName()]);
            }
            $pagination = $pagination->render();
        }
        $this->setData('pagination', $pagination);
    }

    public function setItems($items)
    {
        $this->items = $items;
        $this->itemsLoaded = true;

        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function itemsPerPage()
    {
        return property_exists($this, 'itemsPerPage') ? $this->itemsPerPage : config('clumsy.per-page');
    }

    public function rowClass($item)
    {
        return '';
    }

    public function cellClass($item, $column)
    {
        $classes[] = 'cell-'.$column;

        if ($this->isEditableInline($column)) {
            $classes[] = 'cell-editable-inline';
        }

        if ($item->isCasted($column)) {
            $classes[] = 'cell-'.$item->castAs($column);
        }

        return implode(' ', $classes);
    }

    public function editableInline()
    {
        return property_exists($this, 'editableInline') ? $this->editableInline : [];
    }

    public function isEditableInline($column)
    {
        return in_array($column, $this->editableInline());
    }

    public function columnValue($item, $column)
    {
        $value = $item->$column;

        if (!$item->hasGetMutator($column)) {
            if ($this->isEditableInline($column)) {
                if ($item->isCasted($column)) {
                    switch ($item->castAs($column)) {
                        case 'bool':
                        case 'boolean':
                            return $this->inlineBooleanColumnValue($item, $column);
                    }
                }
            }

            if ($item->isCasted($column) && in_array($item->castAs($column), ['bool', 'boolean'])) {
                return $this->booleanColumnValue($item, $column);
            }
        }

        if ($value === false || $value === null) {
            $value = $this->columnValuePlaceHolder();
        }

        $url = route("{$this->routePrefix}.edit", $item->id);

        return $this->is('gallery') ? $value : HTML::link($url, $value);
    }

    public function columnValuePlaceHolder()
    {
        return '&nbsp;';
    }

    public function inlineBooleanColumnValue($item, $column)
    {
        $macro = $this->is('gallery') ? 'booleanCaption' : 'booleanCell';

        return HTML::$macro($column, $item->$column, [

            'id'    => "ei-{$column}-{$item->id}",
            'field' => [
                'class'       => 'editable-inline',
                'data-id'     => $item->id,
                'data-column' => $column,
            ]

        ], $this->columnName($column));
    }

    public function booleanColumnValue($item, $column)
    {
        return $item->$column == 1 ? trans('clumsy::fields.yes') : trans('clumsy::fields.no');
    }
}
