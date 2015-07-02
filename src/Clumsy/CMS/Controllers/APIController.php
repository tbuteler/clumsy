<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
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

class APIController extends Controller {

    protected $route;
	protected $request;
    protected $action;

    protected $resource;
    protected $model_namespace;
    protected $model_base_name;
    protected $model;

    protected $columns;
    protected $order_equivalence = array();

    protected $model_hierarchy = array(
        'current'  => null,
        'parents'  => array(),
        'children' => array(),
    );

    public $query;

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

    protected function parseParents($model)
    {
        if ($model->isNested())
        {
            $parent_model_base_name = $model->parentModel();

            $parent_model = new $parent_model_base_name;

            array_unshift($this->model_hierarchy['parents'], $parent_model);

            $this->parseParents($parent_model);
        }
    }

    protected function parseChildren($model)
    {
        if ($model->hasChildren())
        {
            foreach ($model->childResources() as $child_resource)
            {
                $child_model_base_name = $model->resourceToModel($child_resource);

                $this->model_hierarchy['children'][] = new $child_model_base_name;
            }
        }
    }

    /**
     * Prepare generic processing of resources base on current route
     */
    public function setupResource(Route $route, Request $request)
    {
        $this->route = $route;
        $this->request = $request;

        $this->admin_prefix = $this->route->getPrefix();

        $this->columns = Config::get('clumsy::default_columns');

        $indicator = $this->route->getName();
        if (!str_contains($indicator, '.'))
        {
            $indicator = str_replace('/', '.', $request->path());
        }

        $indicator = $this->admin_prefix
                     ? str_replace("{$this->admin_prefix}.", '', $indicator)
                     : $indicator;

        if (str_contains($indicator, '.') && !starts_with($indicator, '_'))
        {
            $indicator_array = explode('.', $indicator);
            $this->action = last($indicator_array);
            $this->resource = head($indicator_array);

            $this->model_base_name = studly_case($this->resource);

            if ($this->modelClass())
            {
                $model_name = $this->modelClass();
                $this->model = new $model_name;
                $this->columns = $this->model->columns();
                $this->order_equivalence = $this->model->orderEquivalence();

                $this->model_hierarchy['current'] = $this->model;

                $this->parseParents($this->model);
                $this->parseChildren($this->model);

                $this->query = $this->model->select('*');
            }
        }
    }

	protected function content($content, $status_code = 200)
	{
		return Response::make($content, $status_code);
	}

	protected function code($status_code = 200)
	{
		return $this->content(array(), $status_code);
	}

	protected function getItem($id)
	{
		if (!$item = $this->query->where($this->model->getQualifiedKeyName(), '=', $id)->first())
		{
			return $this->code(404);
		}

		return $item;
	}

	public function index($data = array())
	{
        if (!isset($data['items']))
        {
            $query = !isset($data['query']) ? $this->model->select('*') : $data['query'];
            
            if (!isset($data['sortable']) || $data['sortable'])
            {
                $query->orderSortable();
            }
            
            $per_page = property_exists($this->model, 'admin_per_page') ? $this->model->admin_per_page : Config::get('clumsy::per_page');

            if ($per_page)
            {
                $data['items'] = $query->paginate($per_page);
            }
            else
            {
                $data['items'] = $query->get();
            }
        }

		return $this->content($data['items'], 200);
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
        	return $this->content($validator->messages(), 400);
        }

        foreach ((array)$this->model->booleans() as $check)
        {
            if (!Input::has($check))
            {
                $data[$check] = 0;
            }
        }

        $item = $this->model->create($data);

        // Fire custom Clumsy model events, so controllers can further manipulate
        // items without fear of infinite loops
        Event::fire("clumsy.created: {$this->model_base_name}", array($item));
        Event::fire("clumsy.saved: {$this->model_base_name}", array($item));

        return $this->content($item->id, 200);
    }

    public function show($id)
    {
		$item = $this->getItem($id);

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
		$item = $this->getItem($id);

        $validator = Validator::make($data = Input::all(), $this->model->rules);

        if ($validator->fails())
        {
        	return $this->content($validator->messages(), 400);
        }

        foreach ((array)$this->model->booleans() as $check)
        {
            if (!Input::has($check))
            {
                $data[$check] = 0;
            }
        }

        $item->update($data);

        // Fire custom Clumsy model events, so controllers can further manipulate
        // items without fear of infinite loops
        Event::fire("clumsy.updated: {$this->model_base_name}", array($item));
        Event::fire("clumsy.saved: {$this->model_base_name}", array($item));

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

        if ($item->isRequiredByOthers())
        {
        	return $this->code(400);
        }

        $this->model->destroy($id);

        return $this->code(200);
    }
}