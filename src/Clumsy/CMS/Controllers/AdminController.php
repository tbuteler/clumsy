<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Support\Collection;
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
use Clumsy\Assets\Facade as Asset;
use Clumsy\Eminem\Facade as MediaManager;
use Clumsy\Utils\Facades\HTTP;

class AdminController extends \BaseController {

    protected $resource = '';
    protected $resource_plural = '';
    protected $model_namespace = '';
    protected $model_base_name = '';
    protected $model = '';
    protected $display_name = '';
    protected $display_name_plural = '';

    protected $parent_resource = '';
    protected $parent_model_base_name = '';
    protected $parent_model = '';
    protected $parent_display_name = '';
    protected $parent_display_name_plural = '';

    protected $child_resource = '';
    protected $child_model_base_name = '';
    protected $child_model = '';
    protected $child_display_name = '';

    public function __construct()
    {
        $this->beforeFilter('@setupResource');

        $this->beforeFilter('csrf', array('only' => array('store', 'update', 'destroy')));
    }

    protected function modelClass()
    {
        if ($this->model_namespace && $this->model_base_name)
        {
            return "{$this->model_namespace}\\{$this->model_base_name}";
        }

        return $this->model_base_name;
    }

    /**
     * Prepare generic processing of resources base on current route
     */
    public function setupResource(Route $route, Request $request)
    {
        $this->admin_prefix = Config::get('clumsy::admin_prefix');

        $columns = Config::get('clumsy::default_columns');
        $order_equivalence = array();

        if (strpos($route->getName(), '.'))
        {
            $resource_arr = explode('.', $route->getName());
            $this->resource = $resource_arr[1];
            $this->resource_plural = str_plural($this->resource);
            $this->model_base_name = studly_case($this->resource);

            if ($this->modelClass())
            {
                $model_name = $this->modelClass();
                $this->model = new $model_name;
                $columns = $this->model->columns();
                $order_equivalence = $this->model->orderEquivalence();
            }
        }

        if ($this->model && $this->model->isNested())
        {
            $this->parent_resource = $this->model->parentResource();
            $parent_model_base_name = $this->model->parentModel();
            $this->parent_model_base_name = $parent_model_base_name;
            $this->parent_model = new $parent_model_base_name;
            
            $this->parent_display_name = $this->parent_model->displayName();
            if (!$this->parent_display_name)
            {
                $this->parent_display_name = $this->displayName($this->parent_model_base_name);
            }
            
            $this->parent_display_name_plural = $this->parent_model->displayNamePlural();
            if (!$this->parent_display_name_plural)
            {
                $this->parent_display_name_plural = $this->displayNamePlural($this->parent_model_base_name);
            }
        }

        if ($this->model && $this->model->hasChildren())
        {
            $this->child_resource = $this->model->childResource();
            $child_model_base_name = $this->model->childModel();
            $this->child_model_base_name = $child_model_base_name;
            $this->child_model = new $child_model_base_name;
            $this->child_display_name = $this->child_model->displayNamePlural();
        }

        View::share('admin_prefix', $this->admin_prefix);
        View::share('resource', $this->resource);
        View::share('display_name', $this->displayName());
        View::share('display_name_plural', $this->displayNamePlural());
        View::share('id', $route->getParameter($this->resource));
        View::share('breadcrumb', '');
        View::share('pagination', '');

        View::share('columns', $columns);
        View::share('order_equivalence', $order_equivalence);
        
        View::share('sortable', false);

        Asset::json('admin', array(
            'prefix'   => $this->admin_prefix,
            'resource' => $this->resource,
            'model'    => $this->modelClass(),
        ));
    }

    /**
     * Display a listing of items
     *
     * @return Response
     */
    public function index($data = array())
    {
        if (!isset($data['items']))
        {
            $query = $this->model->select('*')->orderSortable();
            $data['sortable'] = true;
            
            $per_page = property_exists($this->model, 'admin_per_page') ? $this->model->admin_per_page : Config::get('clumsy::per_page');

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
        if (!isset($data['breadcrumb']))
        {
            if (!$this->model->isNested())
            {
                $data['breadcrumb'] = array(
                    trans('clumsy::buttons.home') => URL::to($this->admin_prefix),
                    $this->displayNamePlural()    => URL::route("{$this->admin_prefix}.{$this->resource}.index"),
                    trans('clumsy::buttons.add')  => '',
                );
            }
            else
            {
                $data['breadcrumb'] = array(
                    trans('clumsy::buttons.home') => URL::to($this->admin_prefix),
                    $this->parent_display_name_plural => URL::route("{$this->admin_prefix}.{$this->parent_resource}".'.index'),
                    trans('clumsy::titles.edit_item', array('resource' => $this->parent_display_name)) => URL::route("{$this->admin_prefix}.{$this->parent_resource}".'.edit', Input::get('parent')),
                    $this->displayNamePlural() => URL::route("{$this->admin_prefix}.{$this->parent_resource}".'.edit', Input::get('parent')),
                    trans('clumsy::buttons.add') => '',
                );
            }
        }

        if (!isset($data['title']))
        {
            $data['title'] = trans('clumsy::titles.new_item', array('resource' => $this->displayName()));
        }

        return $this->edit($id = null, $data);
    }

    /**
     * Store a newly created item in storage.
     *
     * @return Response
     */
    public function store()
    {
        $validator = Validator::make($data = Input::all(), $this->model->rules);

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

        foreach ((array)$this->model->booleans() as $check)
        {
            if (!Input::has($check))
            {
                $data[$check] = 0;
            }
        }

        $item = $this->model->create($data);

        $url = URL::route("{$this->admin_prefix}.{$this->resource}.index");

        if ($this->model->isNested())
        {
            $url = URL::route("{$this->admin_prefix}.".$this->model->parentResource().'.edit', $this->model->parentItemId($item->id));
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
        if (!isset($data['item']))
        {
            $data['item'] = $this->model->find($id);
        }

        if (!isset($data['breadcrumb']))
        {
            if (!$this->model->isNested())
            {
                $data['breadcrumb'] = array(
                    trans('clumsy::buttons.home') => URL::to($this->admin_prefix),
                    $this->displayNamePlural()    => URL::route("{$this->admin_prefix}.{$this->resource}.index"),
                    trans('clumsy::buttons.edit') => '',
                );
            }
            else
            {
                $parent_id = $this->model->parentItemId($id);

                $data['breadcrumb'] = array(
                    trans('clumsy::buttons.home') => URL::to($this->admin_prefix),
                    $this->parent_display_name_plural => URL::route("{$this->admin_prefix}.{$this->parent_resource}".'.index'),
                    trans('clumsy::titles.edit_item', array('resource' => $this->parent_display_name)) => URL::route("{$this->admin_prefix}.{$this->parent_resource}".'.edit', $parent_id),
                    $this->displayNamePlural() => URL::route("{$this->admin_prefix}.{$this->parent_resource}".'.edit', $parent_id),
                    trans('clumsy::buttons.edit') => '',
                );
            }
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
        elseif ($id && $this->model->hasChildren())
        {    
            $data['add_child'] = HTTP::queryStringAdd(URL::route("{$this->admin_prefix}.{$this->child_resource}.create"), 'parent', $id);
            $data['child_resource'] = $this->child_resource;
            $data['children_title'] = $this->child_display_name ? $this->displayNamePlural($this->child_display_name) : $this->displayNamePlural($this->child_resource);

            if (!isset($data['children']))
            {
                $query = $this->child_model->select('*')->where($this->child_model->parentIdColumn(), $id)->orderSortable();
                $data['sortable'] = true;

                $per_page = property_exists($this->child_model, 'admin_per_page') ? $this->child_model->admin_per_page : Config::get('clumsy::per_page');

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
                $data['child_columns'] = $this->child_model->columns();
            }

            $view = 'clumsy::templates.edit-nested';
        }
        else
        {
            $view = 'clumsy::templates.edit';
        }

        $data['media'] = MediaManager::slots($this->modelClass(), $id);

        if ($id)
        {
            foreach ((array)$data['item']->required_by as $required)
            {
                if (!method_exists($this->model, $required))
                {
                    throw new \Exception('The model\'s required resources must be defined by a dynamic property with queryable Eloquent relations');
                }
            }
        }

        $data['parent_field'] = null;

        if ($this->model->isNested())
        {
            $parent_id_column = $this->model->parentIdColumn();
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
        $item = $this->model->findOrFail($id);

        $validator = Validator::make($data = Input::all(), $this->model->rules);

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

        foreach ((array)$this->model->booleans() as $check)
        {
            if (!Input::has($check))
            {
                $data[$check] = 0;
            }
        }

        $item->update($data);

        $url = URL::route("{$this->admin_prefix}.{$this->resource}.index");

        if ($this->model->isNested())
        {
            $url = URL::route("{$this->admin_prefix}.".$this->model->parentResource().'.edit', $this->model->parentItemId($id));
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
        $item = $this->model->find($id);

        if ($item->isRequiredByOthers())
        {
            return Redirect::route("{$this->admin_prefix}.{$this->resource}.edit", $id)->with(array(
               'alert_status' => 'warning',
               'alert'        => trans('clumsy::alerts.required_by'),
            ));
        }

        $url = URL::route("{$this->admin_prefix}.{$this->resource}.index");

        if ($this->model->isNested())
        {
            $url = URL::route("{$this->admin_prefix}.".$this->model->parentResource().'.edit', $this->model->parentItemId($id));
        }

        $this->model->destroy($id);

        return Redirect::to($url)->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy::alerts.item_deleted'),
        ));
    }

    public function displayName($model = false)
    {
        if (!$model)
        {
            $model = $this->model ? $this->model->displayName() : false;
            
            if (!$model)
            {
                $model = $this->resource;
            }
        }
        elseif (is_object($model))
        {
            $model = $model->displayName() ? $model->displayName() : class_basename($model);
        }

        return Str::title(str_replace('_', ' ', $model));
    }

    public function displayNamePlural($model = false)
    {
        if (!$model)
        {
            $model = $this->model ? $this->model->displayNamePlural() : false;
            
            if (!$model)
            {
                $model = $this->resource_plural;
            }
        }
        elseif (is_object($model))
        {
            $model = $model->displayNamePlural() ? $model->displayNamePlural() : str_plural(class_basename($model));
        }
        else
        {
            $model = str_plural($model);
        }

        return Str::title(str_replace('_', ' ', $model));
    }
}
