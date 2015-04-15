<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Routing\Route;
use Illuminate\Http\Request;
use Cartalyst\Sentry\Facades\Laravel\Sentry;
use Clumsy\Assets\Facade as Asset;
use Clumsy\Eminem\Facade as MediaManager;
use Clumsy\Utils\Facades\HTTP;
use Clumsy\CMS\Support\Bakery;
use Clumsy\CMS\Support\ResourceNameResolver;
use Clumsy\CMS\Support\ViewResolver;

class AdminController extends APIController {

    protected $resource_plural;

    protected $view;

    public function __construct(ViewResolver $view, Bakery $bakery, ResourceNameResolver $labeler)
    {
        parent::__construct();

        $this->view = $view;

        $this->bakery = $bakery;

        $this->labeler = $labeler;
    }

    /**
     * Prepare generic processing of resources base on current route
     */
    public function setupResource(Route $route, Request $request)
    {
        parent::setupResource($route, $request);

        $this->resource_plural = str_plural($this->resource);

        // Resolve navbar before setting the resource as domain
        View::share('navbar_wrapper', $this->view->resolve('navbar-wrapper'));
        View::share('navbar', $this->view->resolve('navbar'));

        $this->view->setDomain(str_plural($this->resource));
        $this->bakery->setPrefix($this->admin_prefix);

        View::share('model', $this->model);
        View::share('resource', $this->resource);
        
        $id = $route->getParameter($this->resource);
        View::share('id', $id);

        View::share('breadcrumb', $this->bakery->breadcrumb($this->model_hierarchy, $this->action, $id));

        View::share('columns', $this->columns);
        View::share('order_equivalence', $this->order_equivalence);
        
        View::share('sortable', false);
        View::share('pagination', '');

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
        if (!isset($data['columns']))
        {
            $data['columns'] = $this->columns;
        }

        if (!isset($data['items']))
        {
            if ($this->request->ajax()) return parent::index($data);

            $query = !isset($data['query']) ? $this->model->select('*') : $data['query'];

            if ($this->model->filters())
            {
                $query->customFilter();
                $buffer = array();
                $names = array();
                $activeFilters = Session::get("clumsy.filter.{$this->model->resource_name}");
                $hasFilters = false;
                foreach ($this->model->filters() as $column)
                {
                    $original = $column;
                    
                    $equivalence = $this->model->filterEquivalence();
                    $column = array_key_exists($column, $equivalence) ? array_pull($equivalence, $column) : $column;

                    if ($activeFilters != null && array_key_exists($column, $activeFilters))
                    {
                        $hasFilters = true;
                        $buffer[$column] = $activeFilters[$column];
                    }
                    else
                    {
                        $buffer[$column] = null;
                    }

                    // Names
                    if (strpos($column, '.') !== false)
                    {
                        $otherBuffer = explode('.', $column);
                        $modelName = studly_case($otherBuffer[0]);
                        $model = new $modelName();
                        $model_column_names = $model->columnNames();
                        $names[$column] = $model_column_names[$otherBuffer[1]];
                    }
                    else
                    {
                        $column_names = $this->model->columnNames() + $data['columns'];

                        $names[$column] = isset($column_names[$column]) ? $column_names[$column] : $column;
                    }
                }

                $data['filtersData'] = array(
                    'data'       => $this->model->getFilterData($query),
                    'selected'   => $buffer,
                    'hasFilters' => $hasFilters,
                    'names'      => $names,
                );
            }
            
            if (!isset($data['sortable']) || $data['sortable'])
            {
                $query->orderSortable();
                $data['sortable'] = true;
            }
            
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
            $data['title'] = $this->labeler->displayNamePlural($this->model);
        }

        return View::make($this->view->resolve('index'), $data);
    }

    /**
     * Show the form for creating a new item
     *
     * @return Response
     */
    public function create($data = array())
    {
        if (!isset($data['title']))
        {
            $data['title'] = trans('clumsy::titles.new_item', array('resource' => $this->labeler->displayName($this->model)));
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
        $response = parent::store();
        if ($this->request->ajax()) return $response;

        if ($response->getStatusCode() === 400)
        {
            return Redirect::back()
                ->withErrors($response->getOriginalContent())
                ->withInput()
                ->with(array(
                    'alert_status' => 'warning',
                    'alert'        => trans('clumsy::alerts.invalid'),
                ));
        }

        return Redirect::route("{$this->admin_prefix}.{$this->resource}.edit", $response->getOriginalContent())->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy::alerts.item_added'),
        ));
    }

    public function show($id)
    {
        if ($this->request->ajax()) return parent::show($id);

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
            $data['item'] = $id ? $this->model->find($id) : $this->model;
        }

        if (!isset($data['title']))
        {
            $data['title'] = trans('clumsy::titles.edit_item', array('resource' => $this->labeler->displayName($this->model)));
        }

        $data['form_fields'] = $this->view->resolve('fields');

        $view = $this->view->resolve('edit');

        if ($id && $this->model->hasChildren())
        {    
            $child = head($this->model_hierarchy['children']);

            $data['add_child'] = HTTP::queryStringAdd(URL::route("{$this->admin_prefix}.{$child->resource_name}.create"), 'parent', $id);
            $data['child_resource'] = $child->resource_name;
            $data['children_title'] = $this->labeler->displayNamePlural($child);

            if (!isset($data['children']))
            {
                $query = $child->select('*')->where($child->parentIdColumn(), $id)->orderSortable();
                $data['sortable'] = true;

                $per_page = property_exists($child, 'admin_per_page') ? $child->admin_per_page : Config::get('clumsy::per_page');

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
                $data['child_columns'] = $child->columns();
            }

            $view = $this->view->resolve('edit-nested');
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

        if (!isset($data['suppress_delete']))
        {
            $data['suppress_delete'] = $this->model->suppress_delete;
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
        $response = parent::update($id);
        if ($this->request->ajax()) return $response;

        if ($response->getStatusCode() === 400)
        {
            return Redirect::back()
                ->withErrors($response->getOriginalContent())
                ->withInput()
                ->with(array(
                    'alert_status' => 'warning',
                    'alert'        => trans('clumsy::alerts.invalid'),
                ));
        }

        return Redirect::route("{$this->admin_prefix}.{$this->resource}.edit", $id)->with(array(
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
        $item = $this->model->findOrFail($id);

        $url = URL::route("{$this->admin_prefix}.{$this->resource}.index");

        if ($this->model->isNested())
        {
            $url = URL::route("{$this->admin_prefix}.".$this->model->parentResource().'.edit', $this->model->parentItemId($id));
        }

        $response = parent::destroy($id);
        if ($this->request->ajax()) return $response;

        if ($response->getStatusCode() === 400)
        {
            return Redirect::route("{$this->admin_prefix}.{$this->resource}.edit", $id)->with(array(
               'alert_status' => 'warning',
               'alert'        => trans('clumsy::alerts.required_by'),
            ));
        }

        return Redirect::to($url)->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy::alerts.item_deleted'),
        ));
    }
}
