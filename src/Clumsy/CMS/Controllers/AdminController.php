<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use Illuminate\Http\Request;
use Cartalyst\Sentry\Facades\Laravel\Sentry;
use Clumsy\Eminem\Facade as MediaManager;
use Clumsy\Utils\Facades\HTTP;

class AdminController extends \BaseController {

    protected $resource = '';
    protected $resource_plural = '';
    protected $display_name = '';
    protected $namespace = '';
    protected $model = '';

    public function __construct()
    {
        $this->beforeFilter('@setupResource');

        $this->beforeFilter('csrf', array('only' => array('store', 'update', 'destroy')));
    }

    protected function model()
    {
        return "{$this->namespace}\\{$this->model}";
    }

    /**
     * Prepare generic processing of resources base on current route
     */
    public function setupResource(Route $route, Request $request)
    {
        $this->admin_prefix = Config::get('clumsy::admin_prefix');

        if (strpos($route->getName(), '.'))
        {
            $resource_arr = explode('.', $route->getName());
            $this->resource = $resource_arr[1];
            $this->resource_plural = str_plural($this->resource);
            $this->model = studly_case($this->resource);
        }

        View::share('admin_prefix', $this->admin_prefix);
        View::share('resource', $this->resource);
        View::share('display_name', $this->displayName());
        View::share('display_name_plural', $this->displayNamePlural());
        View::share('id', $route->getParameter($this->resource));
        View::share('pagination', '');

        if ($model = $this->model())
        {
            $columns = $model::columns();
        }
        else
        {
            $columns = Config::get('clumsy::default_columns');
        }
        View::share('columns', $columns);
        
        View::share('sortable', false);
    }

    /**
     * Display a listing of items
     *
     * @return Response
     */
    public function index($data = array())
    {
        $model = $this->model();

        if (!isset($data['items']))
        {
            $query = $model::select('*')->orderAuto();
            $per_page = property_exists($model, 'admin_per_page') ? $model::$admin_per_page : Config::get('clumsy::per_page');

            if ($per_page)
            {
                $data['items'] = $query->paginate($per_page);
                $data['pagination'] = $data['items']->links();
            }
            else
            {
                $data['items'] = $query->get();
            }
        }

        if (!isset($data['title']))
        {
            $data['title'] = $this->displayNamePlural();
        }

        if (View::exists("admin.{$this->resource_plural}.index"))
        {
            $view = "admin.{$this->resource_plural}.index";
        }
        else
        {
            $view = 'clumsy::templates.index';
        }

        return View::make($view, $data);
    }

    /**
     * Show the form for creating a new item
     *
     * @return Response
     */
    public function create($data = array())
    {
        return $this->edit($id = null, $data);
    }

    /**
     * Store a newly created item in storage.
     *
     * @return Response
     */
    public function store()
    {
        $model = $this->model();

        $validator = Validator::make($data = Input::all(), $model::$rules);

        if ($validator->fails())
        {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput()
                ->with(array(
                    'alert_status' => 'warning',
                    'alert'        => trans('clumsy::alerts.invalid'),
                ));
        }

        foreach ((array)$model::$booleans as $check)
        {
            if (!Input::has($check))
            {
                $data[$check] = 0;
            }
        }

        $item = $model::create($data);

        $url = URL::route("{$this->admin_prefix}.{$this->resource}.index");

        if ($model::isNested())
        {
            $url = URL::route("{$this->admin_prefix}.".$model::parentResource().'.edit', $model::parentItemId($item->id));
        }

        return Redirect::to($url)->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy::alerts.item_added'),
        ));
    }

    public function show($id)
    {
        return Redirect::route("{$this->admin_prefix}.{$this->resource}.edit", $id);
    }

    /**
     * Show the form for editing the specified item.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id, $data = array())
    {
        $model = $this->model();

        if (!isset($data['item']))
        {
            $data['item'] = $model::find($id);
        }

        if (!isset($data['title']))
        {
            $data['title'] = trans('clumsy::titles.edit_item', array('resource' => $this->displayName()));
        }

        if (View::exists("admin.{$this->resource_plural}.fields"))
        {
            $data['form_fields'] = "admin.{$this->resource_plural}.fields";
        }
        elseif (View::exists("clumsy::{$this->resource_plural}.fields"))
        {
            $data['form_fields'] = "clumsy::{$this->resource_plural}.fields";
        }
        else
        {
            $data['form_fields'] = 'clumsy::templates.fields';
        }
        
        if (View::exists("admin.{$this->resource_plural}.edit"))
        {
            $view = "admin.{$this->resource_plural}.edit";
        }
        elseif (View::exists("clumsy::{$this->resource_plural}.edit"))
        {
            $view = "clumsy::{$this->resource_plural}.edit";
        }
        elseif ($model::hasChildren())
        {    
            $child_resource = $model::childResource();
            $child_model = $model::childModel();
            $child_display_name = $child_model::displayNamePlural();

            $data['add_child'] = HTTP::queryStringAdd(URL::route("{$this->admin_prefix}.$child_resource.create"), 'parent', $id);
            $data['child_resource'] = $child_resource;
            $data['children_title'] = $child_display_name ? $this->displayNamePlural($child_display_name) : $this->displayNamePlural($child_resource);

            if (!isset($data['children']))
            {
                $query = $child_model::select('*')->where($child_model::parentIdColumn(), $id)->orderAuto();
                $per_page = property_exists($child_model, 'admin_per_page') ? $child_model::$admin_per_page : Config::get('clumsy::per_page');

                if ($per_page)
                {
                    $data['children'] = $query->paginate($per_page);
                    $data['pagination'] = $data['children']->links();
                }
                else
                {
                    $data['children'] = $query->get();
                }
            }

            if (!isset($data['child_columns']))
            {
                $data['child_columns'] = $child_model::columns();
            }

            $view = 'clumsy::templates.edit-nested';
        }
        else
        {
            $view = 'clumsy::templates.edit';
        }

        $data['media'] = MediaManager::slots($this->model(), $id);

        if ($id)
        {
            foreach ((array)$data['item']->required_by as $required)
            {
                if (!method_exists($model, $required))
                {
                    throw new \Exception('The model\'s required resources must be defined by a dynamic property with queryable Eloquent relations');
                }
            }
        }

        $data['parent_field'] = null;

        if ($model::isNested())
        {
            $parent_id_column = $model::parentIdColumn();
            $data['parent_field'] = Form::hidden($parent_id_column, $id ? $data['item']->$parent_id_column : Input::get('parent'));
        }

        return View::make($view, $data);
    }

    /**
     * Update the specified item in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $model = $this->model();

        $item = $model::findOrFail($id);

        $validator = Validator::make($data = Input::all(), $model::$rules);

        if ($validator->fails())
        {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput()
                ->with(array(
                    'alert_status' => 'warning',
                    'alert'        => trans('clumsy::alerts.invalid'),
                ));
        }

        foreach ((array)$model::$booleans as $check)
        {
            if (!Input::has($check))
            {
                $data[$check] = 0;
            }
        }

        $item->update($data);

        $url = URL::route("{$this->admin_prefix}.{$this->resource}.index");

        if ($model::isNested())
        {
            $url = URL::route("{$this->admin_prefix}.".$model::parentResource().'.edit', $model::parentItemId($id));
        }

        return Redirect::to($url)->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy::alerts.item_updated'),
        ));
    }

    /**
     * Remove the specified item from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $model = $this->model();

        $item = $model::find($id);

        if ($item->isRequiredByOthers())
        {
            return Redirect::route("{$this->admin_prefix}.{$this->resource}.edit", $id)->with(array(
               'alert_status' => 'warning',
               'alert'        => trans('clumsy::alerts.required_by'),
            ));
        }

        $url = URL::route("{$this->admin_prefix}.{$this->resource}.index");

        if ($model::isNested())
        {
            $url = URL::route("{$this->admin_prefix}.".$model::parentResource().'.edit', $model::parentItemId($id));
        }

        $model::destroy($id);

        return Redirect::to($url)->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy::alerts.item_deleted'),
        ));
    }

    public function displayName($string = false)
    {
        if (!$string)
        {
            $model = $this->model();

            $string = $model ? $model::displayName() : false;
            
            if (!$string)
            {
                $string = $this->resource;
            }
        }

        return Str::title(str_replace('_', ' ', $string));
    }

    public function displayNamePlural($string = false)
    {
        if (!$string)
        {
            $model = $this->model();
            
            $string = $model ? $model::displayNamePlural() : false;
            
            if (!$string)
            {
                $string = $this->resource_plural;
            }
        }
        else
        {
            $string = str_plural($string);
        }

        return Str::title(str_replace('_', ' ', $string));
    }
}
