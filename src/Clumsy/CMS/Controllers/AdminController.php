<?php namespace Clumsy\CMS\Controllers;

use URL;
use View;
use Input;
use Redirect;
use Validator;
use MediaAssociation;
use Illuminate\Routing\Route;
use Illuminate\Http\Request;
use Cartalyst\Sentry\Facades\Laravel\Sentry;

class AdminController extends \BaseController {

    protected $resource = '';
    protected $resource_plural = '';
    protected $display_name = '';
    protected $model = '';

    public function __construct()
    {
        $this->beforeFilter('@setupResource');

        $this->beforeFilter('csrf', array('only' => array('store', 'update', 'destroy')));

        $this->beforeFilter('@setupUser');
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
    }

    public function setupUser()
    {
        $user = Sentry::getUser();

        $username = array_filter(array(
            $user->first_name,
            $user->last_name,
        ));

        if (!count($username))
        {
            $username = (array)$user->email;
        }

        View::share('user', $user);

        View::share('username', implode(' ', $username));

        View::share('usergroup', str_singular($user->getGroups()->first()->name));
    }

    public function setupMedia(&$data, $id = null)
    {
        $defaults = array(
            'association_type'  => $this->model,
            'association_id'    => $id,
        );

        $model = $this->model;

        if ($model::$media)
        {
            if (!is_associative($model::$media))
            {
                foreach ($model::$media as $media)
                {
                    $data['media'][$media['position']] = array_merge(
                        $defaults,
                        array('id' => $media['position']),
                        $media
                    );
                }
            }
            else
            {
                $data['media'][$model::$media['position']] = array_merge(
                    $defaults,
                    array('id' => $model::$media['position']),
                    $model::$media
                );
            }
        }
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

        $this->setupMedia($data);

        $data['form_fields'] = "admin.{$this->resource_plural}.fields";

        if (View::exists("admin.{$this->resource_plural}.edit"))
        {
            $view = "admin.{$this->resource_plural}.edit";
        }
        else
        {    
            $view = 'clumsy/cms::admin.templates.edit';
        }

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
                    'status'   => 'warning',
                    'message'  => trans('clumsy/cms::alerts.invalid'),
                ));
        }

        unset($data['media-bind']);

        $item = $model::create($data);

        if (Input::has('media-bind'))
        {
            foreach (Input::get('media-bind') as $media_id => $attributes)
            {
                if (!$attributes['allow_multiple'])
                {
                    MediaAssociation::where('media_association_type', $this->resource)
                             ->where('media_association_id', $item->id)
                             ->where('position', $attributes['position'])
                             ->delete();
                }

                MediaAssociation::create(array(
                    'media_id'    => $media_id,
                    'media_association_type' => $this->resource,
                    'media_association_id'   => $item->id,
                    'position'    => $attributes['position'],
                ));
            }
        }

        $url = URL::route("admin.{$this->resource}.index");

        if ($model::isNested())
        {
            $url = URL::route('admin.' . $model::parentResource() . '.edit', $model::parentItemId($item->id));
        }

        return Redirect::to($url)->with(array(
           'status'   => 'success',
           'message'  => trans('clumsy/cms::alerts.item_added'),
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

        $this->setupMedia($data, $id);

        $data['form_fields'] = "admin.{$this->resource_plural}.fields";

        if (View::exists("admin.{$this->resource_plural}.edit"))
        {
            $view = "admin.{$this->resource_plural}.edit";
        }
        else
        {    
            $view = 'clumsy/cms::admin.templates.edit';
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
                    'status'   => 'warning',
                    'message'  => trans('clumsy/cms::alerts.invalid'),
                ));
        }

        foreach ((array)$model::$booleans as $check) {

            if (!Input::has($check)) {

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
           'status'   => 'success',
           'message'  => trans('clumsy/cms::alerts.item_updated'),
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
               'status'   => 'warning',
               'message'  => trans('clumsy/cms::alerts.required_by'),
            ));
        }

        $url = URL::route("admin.{$this->resource}.index");

        if ($model::isNested())
        {
            $url = URL::route('admin.' . $model::parentResource() . '.edit', $model::parentItemId($id));
        }

        $model::destroy($id);

        return Redirect::to($url)->with(array(
           'status'   => 'success',
           'message'  => trans('clumsy/cms::alerts.item_deleted'),
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

        return ucwords(str_replace('_', ' ', $string));
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

        return ucwords(str_replace('_', ' ', $string));
    }
}
