<?php namespace Clumsy\CMS\Controllers;

use Redirect;
use Clumsy\CMS\Models\LocalChange;

class ExternalResourceController extends AdminController {

    protected function hasImporter($model = null)
    {
        if ($model === null)
        {
            $model = $this->model;
        }
        
        return (bool)strpos($model::$importer, '@');
    }

    protected function importer($model = null)
    {
        if ($model === null)
        {
            $model = $this->model;
        }

        return explode('@', $model::$importer);
    }

    /**
     * Display a listing of items
     *
     * @return Response
     */
    public function index($data = array())
    {
        $data['importer'] = $this->hasImporter();

        return parent::index($data);
    }

    /**
     * Show the form for creating a new item
     *
     * @return Response
     */
    public function create($data = array())
    {
        $model = $this->model;

        return Redirect::route("admin.{$this->resource}.index")->with(array(
            'status' => 'warning',
            'message' => trans('clumsy/cms::alerts.import.required', array('resources' => $this->displayNamePlural())),
        ));
    }

    /**
     * Update the specified item in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $model = $this->model;
        $resource = $this->resource;

        $model::updating(function($item) use($resource)
        {
            foreach ($item->getDirty() as $field => $value)
            {
                // Double check for changes by removing whitespace (HMTL content can be sneaky)
                if (preg_replace('/\s/', '', $item->getOriginal($field)) === preg_replace('/\s/', '', $item->$field))
                {
                    continue;
                }

                LocalChange::firstOrCreate(array(
                    'resource_type' => $resource,
                    'resource_id' => $item->id,
                    'field' => $field,
                ));
            }
        });

        return parent::update($id);
    }

    public function import($resource_type = null)
    {
        if ($resource_type === null)
        {
            // TODO?: update all
            return Redirect::route('admin.home');
        }

        $model = studly_case($resource_type);

        if ($this->hasImporter($model))
        {
            call_user_func(self::importer($model));
            
            return Redirect::route("admin.$resource_type.index")->with(array(
                'status'  => 'success',
                'message' => trans('clumsy/cms::alerts.import.success', array('resources' => $this->displayNamePlural($resource_type))),
            ));
        }
        else
        {
            return Redirect::route("admin.$resource_type.index")->with(array(
                'status'  => 'warning',
                'message' => trans('clumsy/cms::alerts.import.undefined', array('resources' => $this->displayNamePlural($resource_type))),
            ));
        }
    }
}
