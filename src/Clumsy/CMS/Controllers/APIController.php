<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
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

	protected $request;

    protected $resource = '';
    protected $resource_plural = '';
    protected $model_namespace = '';
    protected $model_base_name = '';
    protected $model = '';
    protected $display_name = '';
    protected $display_name_plural = '';
    
    protected $columns = '';
	protected $order_equivalence = array();

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
        $this->request = $request;

        $this->admin_prefix = Config::get('clumsy::admin_prefix');

        $this->columns = Config::get('clumsy::default_columns');

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
                $this->columns = $this->model->columns();
                $this->order_equivalence = $this->model->orderEquivalence();
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
    }

	protected function content($content, $status_code = 200)
	{
		return Response::make($content, $status_code);
	}

	protected function code($status_code = 200)
	{
		return $this->content([], $status_code);
	}

	protected function getItem($id)
	{
		if (!$item = $this->model->find($id))
		{
			return $this->code(404);
		}

		return $item;
	}

	public function index()
	{
        if (!isset($data['items']))
        {
            $query = !isset($data['query']) ? $this->model->select('*') : $data['query'];
            
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