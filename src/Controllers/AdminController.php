<?php

namespace Clumsy\CMS\Controllers;

use InvalidArgumentException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Collective\Html\FormFacade as Form;
use Clumsy\Utils\Facades\HTTP;
use Clumsy\CMS\Facades\Clumsy;

class AdminController extends APIController
{
    protected $modelHierarchy = [
        'current'  => null,
        'parents'  => [],
        'children' => [],
    ];

    protected $panelTypes = [
        'index' => [
            'table',
        ],
    ];

    protected function parseParents($model, $id = null)
    {
        if ($model->isNested()) {

            // If possible, trace parents back to their actual models and not just a
            // generic class representation
            if (!is_null($id)) {
                $parentModel = $model->parentItem($id);
            } elseif ($parentId = $this->request->get('parent', false)) {
                $parentModel = $model->parentModel()->find($parentId);
            } else {
                $parentModel = $model->parentModel();
            }

            array_unshift($this->modelHierarchy['parents'], $parentModel);

            $this->parseParents($parentModel, $parentModel->getKey());
        }
    }

    protected function parseChildren($model)
    {
        if ($model->hasChildren()) {
            foreach ($model->childResources() as $childResource) {
                $this->modelHierarchy['children'][] = $model->$childResource()->getRelated();
            }
        }
    }

    protected function boot()
    {
        parent::boot();

        if ($this->model) {

            $this->modelHierarchy['current'] = $this->model;

            $this->parseChildren($this->model);

            $id = $this->route->getParameter($this->model->resourceParameter());
            $this->parseParents($this->model, $id);
        }
    }

    protected function getPanelTypes($action)
    {
        $getter = "{$action}PanelTypes";
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        return array_get($this->panelTypes, $action, []);
    }

    protected function currentPanelType($action)
    {
        $panelTypes = array_map('studly_case', $this->getPanelTypes($action));
        if (!empty($panelTypes)) {
            $userView = studly_case(session("clumsy.panel-type.{$this->resource}"));
            if ($userView && in_array($userView, $panelTypes)) {
                return $userView;
            }
            return head((array)$panelTypes);
        }
    }

    protected function loadPanel($action = null)
    {
        if (is_null($action)) {
            $action = $this->action;
        }

        if (!($panel = $this->currentPanelType($action))) {
            $panel = studly_case($action);
        }

        $this->panel = Clumsy::panel("{$this->resource}.{$panel}");

        $this->panel->hierarchy($this->modelHierarchy);
        $this->panel->query($this->query);
    }

    protected function redirectAfter($action, $id = null)
    {
        return null;
    }

    protected function redirectAfterStore($id = null)
    {
        return $this->redirectAfter('store', $id);
    }

    protected function redirectAfterUpdate($id = null)
    {
        return $this->redirectAfter('update', $id);
    }

    /**
     * Display a listing of items
     *
     * @return Response
     */
    public function index()
    {
        if ($this->request->ajax()) {
            return parent::index();
        }

        $this->loadPanel();

        return response($this->panel->render());
    }

    public function indexOfType($type)
    {
        $this->loadPanel('index');
        $this->panel->toggle($type);

        return response($this->panel->render());
    }

    /**
     * Show the form for creating a new item
     *
     * @return Response
     */
    public function create()
    {
        return $this->edit($id = null);
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

        if ($response->getStatusCode() !== 200) {
            $alert = $response->getStatusCode() === 403 ? trans('clumsy::alerts.unauthorized') : trans('clumsy::alerts.invalid');
            return back()
                ->withErrors($response->getOriginalContent())
                ->withInput()
                ->withAlert([
                    'warning' => $alert,
                ]);
        }

        if (!$url = $this->redirectAfterStore($response->getOriginalContent())) {
            $url = route("{$this->routePrefix}.edit", $response->getOriginalContent());
        }

        return redirect($url)->withAlert([
           'success' => trans('clumsy::alerts.item_added'),
        ]);
    }

    public function show($id)
    {
        if ($this->request->ajax()) {
            return parent::show($id);
        }

        return redirect()->route("{$this->routePrefix}.edit", $id);
    }

    /**
     * Show the form for editing the specified item.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $this->loadPanel();

        $this->panel->loadItemById($id);

        if ($id && $this->model->hasChildren()) {

            // Bump current model up on the hierarchy -- it is now a parent
            $parents = $this->modelHierarchy['parents'];
            $parents[] = $this->panel->item;

            $this->panel->template('edit-nested');

            foreach ($this->modelHierarchy['children'] as $child) {
                $childResource = $child->resourceName();
                $panel = $this->request->get('reorder') === $childResource ? 'reorder' : 'table';
                $panel = Clumsy::panel("{$childResource}.{$panel}")->inner();

                $panel->hierarchy(['parents' => $parents, 'current' => $child]);

                // Limit all queries on children to the parent beign edited
                $panel->query($child->query()->where($child->parentIdColumn(), $id));

                $this->panel->nest($panel);
            }
        }

        return response($this->panel->render());
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

        if ($response->getStatusCode() !== 200) {
            $alert = $response->getStatusCode() === 403 ? trans('clumsy::alerts.unauthorized') : trans('clumsy::alerts.invalid');
            return back()
                ->withErrors($response->getOriginalContent())
                ->withInput()
                ->withAlert([
                    'warning' => $alert,
                ]);
        }

        if (!$url = $this->redirectAfterUpdate($id)) {
            $url = route("{$this->routePrefix}.edit", $id);
        }

        return redirect($url)->withAlert([
           'success' => trans('clumsy::alerts.item_updated'),
        ]);
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

        $url = route("{$this->routePrefix}.index");

        if ($this->model->isNested()) {
            $parentResource = $this->model->parentResourceName();
            $url = route("{$this->adminPrefix}.$parentResource.edit", $this->model->parentItemId($id));
            $url = HTTP::queryStringAdd($url, 'show', $this->resource);
        }

        $response = parent::destroy($id);
        if ($this->request->ajax()) {
            return $response;
        }

        if ($response->getStatusCode() !== 200) {
            $alert = $response->getStatusCode() === 403 ? trans('clumsy::alerts.unauthorized') : trans('clumsy::alerts.required_by');
            return redirect()->route("{$this->routePrefix}.edit", $id)->withAlert([
               'warning' => $alert,
            ]);
        }

        return redirect($url)->withAlert([
           'success' => trans('clumsy::alerts.item_deleted'),
        ]);
    }

    public function sort()
    {
        if ($this->request->get('reset') !== null) {
            Session::forget("clumsy.order.{$this->resource}");
        } else {
            Session::put("clumsy.order.{$this->resource}", [$this->request->get('column'), $this->request->get('direction')]);
        }

        return redirect(HTTP::queryStringAdd(URL::previous(), 'show', $this->resource));
    }

    public function reorder()
    {
        if (!$this->model->activeReorder()) {
            return redirect()->route("{$this->adminPrefix}.home");
        }

        $this->loadPanel();

        return response($this->panel->render());
    }

    public function updateOrder()
    {
        if (!$this->allows('update', $this->model)) {
            return back()->withAlert([
                'warning' => trans('clumsy::alerts.unauthorized'),
            ]);
        }

        $order = $this->request->get('order');
        $orderColumn = $this->model->activeReorder();
        foreach ($order as $order => $id) {
            $this->model->where('id', '=', $id)->update([$orderColumn => $order + 1]);
        }

        return back()->withAlert([
           'success' => trans('clumsy::alerts.reorder.success'),
        ]);
    }

    public function filter()
    {
        $filter = [];
        foreach ($this->request->except('_token', 'query_string', 'parent_id') as $column => $values) {
            $column = str_replace(':', '.', $column);
            $filter[$column] = $values;
        }

        $identifier = $this->request->get('parent_id') ? '.'.$this->request->get('parent_id') : null;
        Session::put("clumsy.filter.{$this->resource}{$identifier}", $filter);

        $url = HTTP::queryStringRemove(URL::previous(), 'page');
        $query_string = $this->request->get('query_string');
        if ($query_string && !str_contains($url, $query_string)) {
            $url = HTTP::queryStringAdd($url, $this->request->get('query_string'));
        }

        return redirect($url);
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        try {
            $this->loadPanel();
        } catch (InvalidArgumentException $e) {
            return parent::__call($method, $parameters);
        }

        return response($this->panel->render());
    }
}
