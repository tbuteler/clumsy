<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class BackEndController extends Controller {

    public function reorder($resource)
    {
        if (Input::get('reset') !== null)
        {
            Session::forget("clumsy.order.$resource");
        }
        else
        {
            Session::put("clumsy.order.$resource", array(Input::get('column'), Input::get('direction')));
        }

        return Redirect::back();
    }

    public function update()
    {
        extract(Input::all(), EXTR_SKIP);

        $model = new $model;

        $entry = $model::findOrFail($id);

        switch ($column_type)
        {
            case 'boolean' :
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
        }

        $entry->$column = $value;
        $entry->save();
    }
}