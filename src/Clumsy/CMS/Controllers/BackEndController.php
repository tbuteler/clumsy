<?php
namespace Clumsy\CMS\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Clumsy\Utils\Facades\HTTP;

class BackEndController extends Controller
{
    public function reorder($resource)
    {
        if (Input::get('reset') !== null) {
            Session::forget("clumsy.order.$resource");
        } else {
            Session::put("clumsy.order.$resource", array(Input::get('column'), Input::get('direction')));
        }

        return Redirect::to(HTTP::queryStringAdd(URL::previous(), 'show', $resource));
    }

    public function filter($resource)
    {
        $buffer = array();
        foreach (Input::except('_token') as $column => $values) {
            $column = str_replace(':', '.', $column);
            $buffer[$column] = $values;
        }

        Session::put("clumsy.filter.$resource", $buffer);

        return  Redirect::back();
    }

    public function update()
    {
        extract(Input::all(), EXTR_SKIP);

        $model = new $model;

        $entry = $model::findOrFail($id);

        switch ($column_type) {
            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
        }

        $entry->$column = $value;
        $entry->save();
    }

    public function saveOrder($resource)
    {
        $order = Input::get('order');
        $model = studly_case($resource);

        $resourceObj = new $model;

        foreach ($order as $order => $id) {
            $resourceObj->where('id', '=', $id)->update(array($resourceObj->active_reorder => $order + 1));
        }

        return  Redirect::back()->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy::alerts.reorder.success'),
        ));
    }
}
