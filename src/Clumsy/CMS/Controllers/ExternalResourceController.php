<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Support\Facades\Redirect;
use Clumsy\CMS\Models\LocalChange;

class ExternalResourceController extends AdminController {

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
     * Update the specified item in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $resource = $this->resource;

        $this->model->updating(function($item) use($resource)
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

    protected function hasImporter()
    {        
        return (bool)strpos($this->model->importer, '@');
    }

    protected function fireImport()
    {
        list($class, $method) = explode('@', $this->model->importer);
        return call_user_func(array($class, $method));
    }

    public function import()
    {
        if ($this->hasImporter())
        {
            $import = $this->fireImport();
            
            if ($import !== false)
            {
                if ($import instanceof \Illuminate\Support\MessageBag && $import->any())
                {

                    if ($import->has('error'))
                    {
                        return Redirect::route("{$this->admin_prefix}.{$this->resource}.index")->with(array(
                            'alert_status' => 'warning',
                            'alert'        => $import->first('error'),
                        ));
                    }

                    return Redirect::route("{$this->admin_prefix}.{$this->resource}.index")->with(array(
                        'alert_status' => 'success',
                        'alert'        => $import->first(),
                    ));
                }

                // General success
                return Redirect::route("{$this->admin_prefix}.{$this->resource}.index")->with(array(
                    'alert_status' => 'success',
                    'alert'        => trans('clumsy::alerts.import.success', array('resources' => $this->labeler->displayNamePlural($this->model))),
                ));
            }

            // General failure message
            return Redirect::route("{$this->admin_prefix}.{$this->resource}.index")->with(array(
                'alert_status' => 'warning',
                'alert'        => trans('clumsy::alerts.import.fail', array('resources' => $this->labeler->displayNamePlural($this->model))),
            ));
        }
        else
        {
            return Redirect::route("{$this->admin_prefix}.{$this->resource}.index")->with(array(
                'alert_status' => 'warning',
                'alert'        => trans('clumsy::alerts.import.undefined', array('resources' => $this->labeler->displayNamePlural($this->model))),
            ));
        }
    }
}
