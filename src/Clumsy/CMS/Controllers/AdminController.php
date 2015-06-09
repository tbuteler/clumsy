<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Pagination\Paginator;
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

        $this->view->setDomain($this->resource);
        $this->bakery->setPrefix($this->admin_prefix);
        View::share('view', $this->view);

        View::share('model', $this->model);
        View::share('resource', $this->resource);
        
        $id = $route->getParameter($this->resource);
        View::share('id', $id);

        View::share('breadcrumb', $this->bakery->breadcrumb($this->model_hierarchy, $this->action, $id));

        View::share('columns', $this->columns);
        View::share('order_equivalence', $this->order_equivalence);
        
        View::share('sortable', true);
        View::share('pagination', '');

        View::share('toggle_filters', false);

        Asset::json('admin', array(
            'prefix'   => $this->admin_prefix,
            'resource' => $this->resource,
            'model'    => $this->modelClass(),
        ));
    }

    protected function redirectAfter($action, $id = null)
    {
        return null;
    }

    protected function redirectAfterStore($id = null)
    {
        return null;
    }

    protected function redirectAfterUpdate($id = null)
    {
        return null;
    }

    protected function hasTypeCounter($type)
    {
        return method_exists($this, 'typeCount'.studly_case($type));
    }

    protected function typeCount($type)
    {
        $method = 'typeCount'.studly_case($type);
        return $this->{$method}();
    }

    protected function typeCounts()
    {
        $counts['all'] = $this->hasTypeCounter('all') ? $this->typeCount('all') : $this->model->managed()->count();
        
        foreach ($this->model->toggleFilters() as $filter => $filter_label)
        {
            if ($filter === 'all')
            {
                continue;
            }

            $counts[$filter] = $this->hasTypeCounter($filter) ? $this->typeCount($filter) : $this->model->managed()->ofType($filter)->count();
        }

        return $counts;
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

        $query = !isset($data['query']) ? $this->model->select('*')->managed() : $data['query'];

        if ($this->model->filters())
        {
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

        if (!isset($data['items']))
        {
            if ($this->request->ajax()) return parent::index($data);

            $data['items'] = $query->getPaged();
        }

        if (!isset($data['pagination']) && $data['items'] instanceof Paginator)
        {
            $data['pagination'] = $data['items']->links();
        }

        if (!isset($data['title']))
        {
            $data['title'] = $this->labeler->displayNamePlural($this->model);
        }

        if ($this->model->toggleFilters())
        {
            $data['index_type'] = isset($data['index_type']) ? $data['index_type'] : false;
            $data['item_count'] = $this->typeCounts();

            $data['toggle_filters'] = $this->model->toggleFilters();
        }

        $data['reorder'] = (bool)$this->model->active_reorder;

        return View::make($this->view->resolve('index'), $data);
    }

    public function indexOfType($type, $data = array())
    {
        $data['index_type'] = $type;
        $data['item_count'] = $this->typeCounts();

        if (!isset($data['query']))
        {
            $data['query'] = $this->model->select('*')->managed();
        }

        if (!isset($data['columns']))
        {
            $data['columns'] = $this->columns;

            if (sizeof($this->model->suppressWhenToggled()))
            {
                foreach ($this->model->suppressWhenToggled() as $suppress)
                {
                    unset($data['columns'][$suppress]);
                }
            }

            if (sizeof($this->model->appendWhenToggled()))
            {
                foreach ($this->model->appendWhenToggled() as $append => $append_label)
                {
                    $data['columns'][$append] = $append_label;
                }
            }
        }

        $data['query']->ofType($type);

        return $this->index($data);
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

        if (!$url = $this->redirectAfterStore($response->getOriginalContent()))
        {
            $url = URL::route("{$this->admin_prefix}.{$this->resource}.edit", $response->getOriginalContent());
        }

        return Redirect::to($url)->with(array(
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

            $data['child_resource'] = $child->resource_name;
            
            if (!isset($data['add_child']))
            {
                $data['add_child'] = HTTP::queryStringAdd(URL::route("{$this->admin_prefix}.{$child->resource_name}.create"), 'parent', $id);
            }

            if (!isset($data['children_title']))
            {
                $data['children_title'] = $this->labeler->displayNamePlural($child);
            }

            if (!isset($data['children']))
            {
                $query = $child->select('*')->where($child->parentIdColumn(), $id)->orderSortable();
                $data['sortable'] = true;

                $per_page = property_exists($child, 'admin_per_page') ? $child->admin_per_page : Config::get('clumsy::per_page');

                if ($per_page)
                {
                    $data['children'] = $query->paginate($per_page);
                    $data['pagination'] = $data['children']->fragment($child->resource_name)->links();
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

        if (!isset($data['fields']))
        {
            $data['fields'] = array();
        }

        if (!isset($data['buttons']))
        {
            $data['buttons'] = array();
        }

        if ($this->model->isNested())
        {
            $parent_id_column = $this->model->parentIdColumn();
            $data['fields'][] = Form::hidden($parent_id_column, $id ? $data['item']->$parent_id_column : Input::get('parent'));
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

        if (!$url = $this->redirectAfterUpdate($id))
        {
            $url = URL::route("{$this->admin_prefix}.{$this->resource}.edit", $id);
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

    public function reorder($resource)
    {
        if (!isset($this->model->active_reorder))
        {
            return Redirect::route("{$this->admin_prefix}.home");
        }

        $data['columns'] = $this->model->reorderColumns();

        $query = $this->model->select('*')->orderBy($this->model->active_reorder, 'asc');

        $data['items'] = $query->get();

        $data['title'] = trans('clumsy::titles.reorder', array('resources' => $this->labeler->displayNamePlural($this->model)));

        Asset::enqueue('jquery-ui', 30);

        return View::make($this->view->resolve('reorder'), $data);
    }
}
