<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Support\Facades\Redirect;
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
        return Redirect::route("{$this->admin_prefix}.{$this->resource}.index")->with(array(
            'alert_status' => 'warning',
            'alert'        => trans('clumsy::alerts.import.required', array('resources' => $this->displayNamePlural())),
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
        $model = $this->modelClass();
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
                    'resource_id'   => $item->id,
                    'field'         => $field,
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
            return Redirect::route("{$this->admin_prefix}.home");
        }

        $model = studly_case($resource_type);

        if ($this->hasImporter($model))
        {
            $import = call_user_func(self::importer($model));
            
            if ($import !== false)
            {
                if ($import instanceof \Illuminate\Support\MessageBag && $import->any())
                {

                    if ($import->has('error'))
                    {
                        return Redirect::route("{$this->admin_prefix}.$resource_type.index")->with(array(
                            'alert_status' => 'warning',
                            'alert'        => $import->first('error'),
                        ));
                    }

                    return Redirect::route("{$this->admin_prefix}.$resource_type.index")->with(array(
                        'alert_status' => 'success',
                        'alert'        => $import->first(),
                    ));
                }

                // General success
                return Redirect::route("{$this->admin_prefix}.$resource_type.index")->with(array(
                    'alert_status' => 'success',
                    'alert'        => trans('clumsy::alerts.import.success', array('resources' => $this->displayNamePlural($resource_type))),
                ));
            }

            // General failure message
            return Redirect::route("{$this->admin_prefix}.$resource_type.index")->with(array(
                'alert_status' => 'warning',
                'alert'        => trans('clumsy::alerts.import.fail', array('resources' => $this->displayNamePlural($resource_type))),
            ));
        }
        else
        {
            return Redirect::route("{$this->admin_prefix}.$resource_type.index")->with(array(
                'alert_status' => 'warning',
                'alert'        => trans('clumsy::alerts.import.undefined', array('resources' => $this->displayNamePlural($resource_type))),
            ));
        }
    }
}
