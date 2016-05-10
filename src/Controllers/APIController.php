<?php

namespace Clumsy\CMS\Controllers;

use InvalidArgumentException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Application;
use Clumsy\Assets\Facade as Asset;
use Clumsy\Eminem\Facade as MediaManager;
use Clumsy\CMS\Facades\Clumsy;
use Clumsy\CMS\Models\BaseModel;
use Clumsy\CMS\Facades\Overseer;

class APIController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected $app;
    protected $request;
    protected $route;
    protected $action;

    protected $adminPrefix;
    protected $routePrefix;

    protected $resource;
    protected $modelClass;
    protected $modelBaseName;
    protected $model;

    protected $query;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->request = $this->app['request'];
        $this->route = $this->request->route();

        $this->boot();
    }

    protected function modelClass($resource = null)
    {
        $baseName = $resource ? studly_case($resource) : $this->modelBaseName;

        if ($baseName) {
            // If namespace has been specified in the controller, use it blindly
            if ($this->modelClass) {
                return "{$this->modelClass}";
            }

            // If not, use sensible defaults but check for class existence before returning
            $baseNamespace = '';
            foreach (['App', 'Models'] as $namespace) {
                $baseNamespace .= "\\{$namespace}";
                if (class_exists("$baseNamespace\\{$baseName}")) {
                    $this->modelClass = "$baseNamespace\\{$baseName}";
                    return $this->modelClass;
                }
            }
        }

        return $baseName;
    }

    /**
     * Prepare generic processing of resources base on current route
     */
    protected function boot()
    {
        $this->adminPrefix = Clumsy::prefix();

        $indicator = $this->route ? $this->route->getName() : null;

        if (!str_contains($indicator, '.')) {
            $indicator = ltrim(str_replace('/', '.', request()->path()), '.');
        }

        $indicator = $this->adminPrefix
                     ? str_replace("{$this->adminPrefix}.", '', $indicator)
                     : $indicator;

        if (str_contains($indicator, '.') && !starts_with($indicator, '_')) {
            $indicatorArray = explode('.', $indicator);
            $this->action = last($indicatorArray);
            $this->resource = head($indicatorArray);
            $this->modelBaseName = studly_case($this->resource);

            if ($modelName = $this->modelClass()) {

                $this->model = new $modelName;

                $this->query = $this->model->select('*');

                $this->routePrefix = $this->model->resourceName();
                if ($this->adminPrefix) {
                    $this->routePrefix = $this->adminPrefix.'.'.$this->routePrefix;
                }
            }
        }
    }

    protected function hasPolicy($item)
    {
        try {

            policy($item);

        } catch (InvalidArgumentException $e) {

            return false;
        }

        return true;
    }

    protected function getPolicyItem($item)
    {
        return $this->hasPolicy($item) ? $item : BaseModel::class;
    }

    protected function allows($action, $item)
    {
        try {

            $this->authorize($action, $this->getPolicyItem($item));

        } catch (AuthorizationException $e) {

            return false;
        }

        return true;
    }

    protected function content($content, $status_code = 200)
    {
        return response($content, $status_code);
    }

    protected function code($status_code = 200)
    {
        return $this->content([], $status_code);
    }

    protected function getItem($id)
    {
        if (!$item = $this->query->where($this->model->getQualifiedKeyName(), '=', $id)->first()) {
            return $this->code(404);
        }

        return $item;
    }

    public function index()
    {
        if (!$this->allows('index', $this->model)) {
            return $this->code(403);
        }

        return $this->content($this->query->getPaged(), 200);
    }

    /**
     * Store a newly created item in storage.
     *
     * @return Response
     */
    public function store()
    {
        if (!$this->allows('store', $this->model)) {
            return $this->code(403);
        }

        $validator = Validator::make($data = Input::all(), $this->model->rules());

        if ($validator->fails()) {
            return $this->content($validator->messages(), 400);
        }

        foreach ($this->model->booleans() as $check) {
            if (!Input::has($check)) {
                $data[$check] = 0;
            }
        }

        $item = $this->model->create($data);

        // Fire custom Clumsy model events, so controllers can further manipulate
        // items without fear of infinite loops
        Event::fire("clumsy.created: {$this->modelBaseName}", [$item]);
        Event::fire("clumsy.saved: {$this->modelBaseName}", [$item]);

        return $this->content($item->id, 200);
    }

    public function show($id)
    {
        $item = $this->getItem($id);

        if (!$this->allows('show', $item)) {
            return $this->code(403);
        }

        return $this->content($item->id, 200);
    }

    /**
     * Update the specified item in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $data = Input::all();

        // Use existing model for validation to account for conditional rules
        $item = $this->getItem($id);

        if (!$this->allows('update', $item)) {
            return $this->code(403);
        }

        // Allow partial update/validation
        $rules = array_only($item->rules(), array_keys($data));

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->content($validator->messages(), 400);
        }

        foreach ($this->model->booleans() as $check) {
            if (!Input::has($check)) {
                $data[$check] = 0;
            } else {
                $item[$check] = 'boolean';
            }
        }

        $item->update($data);

        // Fire custom Clumsy model events, so controllers can further manipulate
        // items without fear of infinite loops
        Event::fire("clumsy.updated: {$this->modelBaseName}", [$item]);
        Event::fire("clumsy.saved: {$this->modelBaseName}", [$item]);

        return $this->code(200);
    }

    /**
     * Remove the specified item from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $item = $this->getItem($id);

        if (!$this->allows('destroy', $item)) {
            return $this->code(403);
        }

        if ($item->isRequiredByOthers()) {
            return $this->code(400);
        }

        $this->model->destroy($id);

        return $this->code(200);
    }
}
