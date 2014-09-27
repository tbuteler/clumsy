<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class BackEndController extends \BaseController {

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

    public function update($model_class)
    {
        $model_class = urldecode($model_class);
        $model = new $model_class;

        extract(Input::all(), EXTR_SKIP);

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