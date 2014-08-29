<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
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
    protected $model = '';

    public function __construct()
    {
        $this->beforeFilter('@setupResource');

        $this->beforeFilter('csrf', array('only' => array('store', 'update', 'destroy')));
    }

    /**
     * Prepare generic processing of resources base on current route
     */
    public function setupResource(Route $route, Request $request)
    {
        if (strpos($route->getName(), '.'))
        {
            $resource_arr = explode('.', $route->getName());
            $this->resource = $resource_arr[1];
            $this->resource_plural = str_plural($this->resource);
            $this->model = studly_case($this->resource);
        }

        View::share('resource', $this->resource);
        View::share('display_name', $this->displayName());
        View::share('display_name_plural', $this->displayNamePlural());
        View::share('id', $route->getParameter($this->resource));
        View::share('properties', Config::get('cms::default_columns'));
    }

    /**
     * Display a listing of items
     *
     * @return Response
     */
    public function index($data = array())
    {
        $model = $this->model;

        if (!isset($data['items']))
        {
            $model = $this->model;
            $data['items'] = $model::orderBy('id', 'desc')->get();
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
            $view = 'clumsy/cms::admin.templates.index';
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
        $model = $this->model;

        if (!isset($data['title']))
        {
            $data['title'] = trans('clumsy/cms::titles.new_item', array('resource' => $this->displayName()));
        }

        $data['form_fields'] = "admin.{$this->resource_plural}.fields";

        if (View::exists("admin.{$this->resource_plural}.edit"))
        {
            $view = "admin.{$this->resource_plural}.edit";
        }
        else
        {    
            $view = 'clumsy/cms::admin.templates.edit';
        }

        $data['media'] = MediaManager::slots($this->model);

        return View::make($view, $data);
    }

    /**
     * Store a newly created item in storage.
     *
     * @return Response
     */
    public function store()
    {
        $model = $this->model;

        $validator = Validator::make($data = Input::all(), $model::$rules);

        if ($validator->fails())
        {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput()
                ->with(array(
                    'alert_status' => 'warning',
                    'alert'        => trans('clumsy/cms::alerts.invalid'),
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

        $url = URL::route("admin.{$this->resource}.index");

        if ($model::isNested())
        {
            $url = URL::route('admin.' . $model::parentResource() . '.edit', $model::parentItemId($item->id));
        }

        return Redirect::to($url)->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy/cms::alerts.item_added'),
        ));
    }

    public function show($id)
    {
        return Redirect::route("admin.{$this->resource}.edit", $id);
    }

    /**
     * Show the form for editing the specified item.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id, $data = array())
    {
        $model = $this->model;

        if (!isset($data['item']))
        {
            $data['item'] = $model::find($id);
        }

        if (!isset($data['title']))
        {
            $data['title'] = trans('clumsy/cms::titles.edit_item', array('resource' => $this->displayName()));
        }

        $data['form_fields'] = "admin.{$this->resource_plural}.fields";

        if (View::exists("admin.{$this->resource_plural}.edit"))
        {
            $view = "admin.{$this->resource_plural}.edit";
        }
        elseif ($model::hasChildren())
        {    
            $child_resource = $model::childResource();
            $child_model = $model::childModel();
            $child_display_name = $child_model::displayNamePlural();

            $data['add_child'] = HTTP::queryStringAdd(URL::route("admin.$child_resource.create"), 'parent', $id);
            $data['child_resource'] = $child_resource;
            $data['children_title'] = $child_display_name ? $this->displayNamePlural($child_display_name) : $this->displayNamePlural($child_resource);

            if (!isset($data['children']))
            {
                $data['children'] = $child_model::all();
            }

            if (!isset($data['child_properties']))
            {
                $data['child_properties'] = Config::get('cms::default_columns');
            }

            $view = 'clumsy/cms::admin.templates.edit-nested';
        }
        else
        {
            $view = 'clumsy/cms::admin.templates.edit';
        }

        $data['media'] = MediaManager::slots($this->model, $id);

        foreach ((array)$data['item']->required_by as $required)
        {
            if (!method_exists($model, $required))
            {
                throw new \Exception('The model\'s required resources must be defined by a dynamic property with queryable Eloquent relations');
            }
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
        $model = $this->model;

        $item = $model::findOrFail($id);

        $validator = Validator::make($data = Input::all(), $model::$rules);

        if ($validator->fails())
        {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput()
                ->with(array(
                    'alert_status' => 'warning',
                    'alert'        => trans('clumsy/cms::alerts.invalid'),
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

        $url = URL::route("admin.{$this->resource}.index");

        if ($model::isNested())
        {
            $url = URL::route('admin.' . $model::parentResource() . '.edit', $model::parentItemId($id));
        }

        return Redirect::to($url)->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy/cms::alerts.item_updated'),
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
        $model = $this->model;

        $item = $model::find($id);

        if ($item->isRequiredByOthers())
        {
            return Redirect::route("admin.{$this->resource}.edit", $id)->with(array(
               'alert_status' => 'warning',
               'alert'        => trans('clumsy/cms::alerts.required_by'),
            ));
        }

        $url = URL::route("admin.{$this->resource}.index");

        if ($model::isNested())
        {
            $url = URL::route('admin.' . $model::parentResource() . '.edit', $model::parentItemId($id));
        }

        $model::destroy($id);

        return Redirect::to($url)->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy/cms::alerts.item_deleted'),
        ));
    }

    public function displayName($string = false)
    {
        if (!$string)
        {
            $model = $this->model;

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
            $model = $this->model;
            
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
