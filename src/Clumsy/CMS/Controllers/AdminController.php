<?php
namespace Clumsy\CMS\Controllers;

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
use Clumsy\Assets\Facade as Asset;
use Clumsy\Eminem\Facade as MediaManager;
use Clumsy\Utils\Facades\HTTP;
use Clumsy\CMS\Support\Bakery;
use Clumsy\CMS\Support\ResourceNameResolver;
use Clumsy\CMS\Support\ViewResolver;

class AdminController extends APIController
{
    protected $resource_plural;

    protected $view;

    public function __construct(ViewResolver $view, Bakery $bakery, ResourceNameResolver $labeler)
    {
        parent::__construct();

        $this->view = clone $view;

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

        $this->view->setDomain($this->resource)->pushLevel($this->action);
        View::share('view', $this->view);

        $this->bakery->setPrefix($this->admin_prefix);

        View::share('action', $this->action);

        View::share('model', $this->model);
        View::share('resource', $this->resource);
        View::share('model_class', $this->modelClass());
        View::share('is_child', false);

        $id = $route->getParameter($this->resource);
        View::share('id', $id);

        View::share('breadcrumb', $this->bakery->breadcrumb($this->model_hierarchy, $this->action, $id));

        View::share('order_equivalence', $this->model->orderEquivalence());

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

        foreach ($this->model->toggleFilters() as $filter => $filter_label) {
            if ($filter === 'all') {
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
        if (isset($data['query'])) {
            $this->query = $data['query'];
        }

        $data['innerView'] = !isset($data['innerView']) ? $this->model->currentInnerView() : $data['innerView'];
        $this->setContext('inner_view', $data['innerView']);

        $this->query->managed();

        if (!isset($data['columns'])) {
            $data['columns'] = $this->model->columns();
        }

        $data['filterData'] = $this->generateFilterData($this->query, $this->model, $data);

        if (!isset($data['items'])) {
            if ($this->request->ajax()) {
                return parent::index($data);
            }

            $data['items'] = $this->query->getPaged();
        }

        if (!isset($data['pagination']) && $data['items'] instanceof Paginator) {
            $data['pagination'] = $data['items']->links();
        }

        if (!isset($data['title'])) {
            $data['title'] = $this->labeler->displayNamePlural($this->model);
        }

        if ($this->model->toggleFilters()) {
            $data['index_type'] = isset($data['index_type']) ? $data['index_type'] : false;
            $data['item_count'] = $this->typeCounts();

            $data['toggle_filters'] = $this->model->toggleFilters();
        }

        $data['reorder'] = (bool)$this->model->active_reorder;

        return View::make($this->view->resolve('index'), $data);
    }

    public function indexOfType($type, $data = array())
    {
        $this->setContext('of_type', $type);

        $this->view->nestLevel($type)->pushLevel('index');

        $data['index_type'] = $type;
        $data['item_count'] = $this->typeCounts();

        if (isset($data['query'])) {
            $this->query = $data['query'];
            unset($data['query']);
        }

        if (!isset($data['columns'])) {
            $data['columns'] = $this->model->columns();

            if (sizeof($this->model->suppressWhenToggled())) {
                foreach ($this->model->suppressWhenToggled() as $suppress) {
                    unset($data['columns'][$suppress]);
                }
            }

            if (sizeof($this->model->appendWhenToggled())) {
                foreach ($this->model->appendWhenToggled() as $append => $append_label) {
                    $data['columns'][$append] = $append_label;
                }
            }
        }

        $this->query->ofType($type);

        return $this->index($data);
    }

    /**
     * Show the form for creating a new item
     *
     * @return Response
     */
    public function create($data = array())
    {
        if (!isset($data['title'])) {
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
        if ($this->request->ajax()) {
            return $response;
        }

        if ($response->getStatusCode() === 400) {
            return Redirect::back()
                ->withErrors($response->getOriginalContent())
                ->withInput()
                ->with(array(
                    'alert_status' => 'warning',
                    'alert'        => trans('clumsy::alerts.invalid'),
                ));
        }

        if (!$url = $this->redirectAfterStore($response->getOriginalContent())) {
            $url = route("{$this->admin_prefix}.{$this->resource}.edit", $response->getOriginalContent());
        }

        return Redirect::to($url)->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy::alerts.item_added'),
        ));
    }

    public function show($id)
    {
        if ($this->request->ajax()) {
            return parent::show($id);
        }

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
        if (!isset($data['item'])) {
            $data['item'] = $id ? $this->model->find($id) : $this->model;
        }

        if (!isset($data['title'])) {
            $data['title'] = trans('clumsy::titles.edit_item', array('resource' => $this->labeler->displayName($this->model)));
        }

        $view = $this->view->resolve('edit');

        if ($id && $this->model->hasChildren()) {
            if (!isset($data['children'])) {
                $data['children'] = array();
            }

            foreach ($this->model_hierarchy['children'] as $child) {
                $child_resource = $child->resource_name;

                if (!isset($data['children'][$child_resource])) {
                    $data['children'][$child_resource] = array();
                }

                $data['children'][$child_resource]['resource'] = $child_resource;

                $reorder = false;
                $reorder_link = false;
                if ($child->active_reorder) {
                    if (Input::get('reorder') === $child_resource) {
                        $reorder = true;
                    } else {
                        $reorder_link = HTTP::queryStringAdd(route("{$this->admin_prefix}.{$this->resource}.edit", $id), 'reorder', $child_resource);
                    }
                }

                $child->setAdminContext('inner_view', $reorder ? 'reorder' : $child->currentInnerView());
                $default_query = !isset($data['children'][$child_resource]) || !isset($data['children'][$child_resource]['items']);

                $data['children'][$child_resource] = array_merge(array(
                    'is_child'     => true,
                    'title'        => $this->labeler->displayNamePlural($child),
                    'model_class'  => $this->modelClass($child_resource),
                    'columns'      => $child->columns(),
                    'back_link'    => HTTP::queryStringAdd(route("{$this->admin_prefix}.{$this->resource}.edit", $id), 'show', $child_resource),
                    'create_link'  => HTTP::queryStringAdd(route("{$this->admin_prefix}.{$child_resource}.create"), 'parent', $id),
                    'innerView'    => $reorder ? 'reorder' : $child->currentInnerView(),
                    'reorder'      => (bool)$reorder_link,
                    'reorder_link' => $reorder_link,

                ), $data['children'][$child_resource]);

                if ($reorder || $default_query) {
                    $child_query = $child->query();
                    $child_query->withAdminContext('innerView', $data['children'][$child_resource]['innerView'])
                                ->where($child->parentIdColumn(), $id);
                }

                $data['children'][$child_resource]['filterData'] = $this->generateFilterData($child_query, $child, $data['children'][$child_resource], $id);

                if ($reorder) {
                    $data['children'][$child_resource]['items'] = $child_query->orderBy('order', 'asc')->get();
                    $data['children'][$child_resource]['tab_title'] = $data['children'][$child_resource]['title'];
                    $data['children'][$child_resource]['title'] = trans('clumsy::titles.reorder', array('resources' => $this->labeler->displayNamePlural($child)));

                    Asset::enqueue('jquery-ui', 30);

                } elseif ($default_query) {
                    $child_query->managed($id);
                    $data['children'][$child_resource]['sortable'] = true;

                    $per_page = property_exists($child, 'admin_per_page') ? $child->admin_per_page : Config::get('clumsy::per_page');
                    if ($per_page) {
                        $child_items = $child_query->paginate($per_page);
                        $data['children'][$child_resource]['items'] = $child_items;
                        $data['children'][$child_resource]['pagination'] = $child_items->appends(array('show' => $child_resource))->links();
                    } else {
                        $data['children'][$child_resource]['items'] = $child_query->get();
                    }
                }

                $child_view = clone $this->view;
                $child_view->setDomain($child->resource_name)->clearLevels()->pushLevel($reorder ? 'reorder' : 'index');
                $data['children'][$child_resource]['view'] = $child_view;

                $child_data = array_merge(
                    $data,
                    $data['children'][$child_resource]
                );

                $child_template = $reorder ? 'inner-reorder' : 'inner-index';
                $data['children'][$child_resource]['child_template'] = $child_template;
                $data['children'][$child_resource]['inner-template'] = View::make($child_view->resolve($child_template), $child_data)->render();
            }

            $view = $this->view->resolve('edit-nested');
        }

        $data['media'] = MediaManager::slots($this->modelClass(), $id);

        if ($id) {
            foreach ((array)$data['item']->required_by as $required) {
                if (!method_exists($this->model, $required)) {
                    throw new \Exception('The model\'s required resources must be defined by a dynamic property with queryable Eloquent relations');
                }
            }
        }

        if (!isset($data['fields'])) {
            $data['fields'] = array();
        }

        if (!isset($data['buttons'])) {
            $data['buttons'] = array();
        }

        if ($this->model->isNested()) {
            $parent_resource = $this->model->parentResource();
            $parent_id_column = $this->model->parentIdColumn();
            $parent_id = $id ? $data['item']->$parent_id_column : Input::get('parent');
            $data['fields'][] = Form::hidden($parent_id_column, $parent_id);
            $data['back_link'] = HTTP::queryStringAdd(route("{$this->admin_prefix}.{$parent_resource}.edit", $parent_id), 'show', $this->resource);
        } else {
            $data['back_link'] = route("{$this->admin_prefix}.{$this->resource}.index");
        }

        if (!isset($data['suppress_delete'])) {
            $data['suppress_delete'] = $this->model->suppress_delete;
        }

        $data['show_resource'] = Input::get('show', Input::get('reorder'));

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
        if ($this->request->ajax()) {
            return $response;
        }

        if ($response->getStatusCode() === 400) {
            return Redirect::back()
                ->withErrors($response->getOriginalContent())
                ->withInput()
                ->with(array(
                    'alert_status' => 'warning',
                    'alert'        => trans('clumsy::alerts.invalid'),
                ));
        }

        if (!$url = $this->redirectAfterUpdate($id)) {
            $url = route("{$this->admin_prefix}.{$this->resource}.edit", $id);
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

        $url = route("{$this->admin_prefix}.{$this->resource}.index");

        if ($this->model->isNested()) {
            $url = route("{$this->admin_prefix}.".$this->model->parentResource().'.edit', $this->model->parentItemId($id));
        }

        $response = parent::destroy($id);
        if ($this->request->ajax()) {
            return $response;
        }

        if ($response->getStatusCode() === 400) {
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

    public function reorder()
    {
        if (!isset($this->model->active_reorder) || !$this->model->active_reorder) {
            return Redirect::route("{$this->admin_prefix}.home");
        }

        $this->setContext('inner_view', 'reorder');

        $this->query->orderBy($this->model->active_reorder, 'asc');

        $data['columns'] = $this->model->columns();
        $data['items'] = $this->query->get();
        $data['title'] = trans('clumsy::titles.reorder', array('resources' => $this->labeler->displayNamePlural($this->model)));

        Asset::enqueue('jquery-ui', 30);

        return View::make($this->view->resolve('reorder'), $data);
    }

    public function generateFilterData($query, $model = null, $data = array(), $parent_id = null)
    {
        if (!$model) {
            $model = $this->model;
        }

        if (!$model->filters()) {
            return false;
        }

        $buffer = array();
        $names = array();
        $identifier = $parent_id ? ".{$parent_id}" : null;
        $activeFilters = Session::get("clumsy.filter.{$model->resource_name}{$identifier}");

        $hasFilters = false;
        foreach ($model->filters() as $column) {
            $original = $column;

            $equivalence = $model->filterEquivalence();
            $column = array_key_exists($column, $equivalence) ? array_pull($equivalence, $column) : $column;

            if ($activeFilters != null && array_key_exists($column, $activeFilters)) {
                $hasFilters = true;
                $buffer[$column] = $activeFilters[$column];
            } else {
                $buffer[$column] = null;
            }

            // Names
            if (strpos($column, '.') !== false) {
                $otherBuffer = explode('.', $column);
                $modelName = studly_case($otherBuffer[0]);
                $relatedModel = new $modelName();
                $model_column_names = $relatedModel->columnNames();
                $names[$column] = $model_column_names[$otherBuffer[1]];
            } else {
                $column_names = $model->columnNames() + (isset($data['columns']) ? $data['columns'] : array());
                $names[$column] = isset($column_names[$column]) ? $column_names[$column] : $column;
            }
        }

        return array(
            'data'       => $model->getFilterData($query),
            'selected'   => $buffer,
            'hasFilters' => $hasFilters,
            'names'      => $names,
        );
    }
}
