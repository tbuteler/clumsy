<?php
namespace Clumsy\CMS\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Routing\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;
use Clumsy\CMS\Facades\Overseer;
use Clumsy\CMS\Controllers\AdminController;
use Clumsy\CMS\Support\Bakery;
use Clumsy\CMS\Support\ResourceNameResolver;
use Clumsy\CMS\Support\ViewResolver;

class UsersController extends AdminController
{
    public function __construct(ViewResolver $view, Bakery $bakery, ResourceNameResolver $labeler)
    {
        $this->model_namespace = '\Clumsy\CMS\Models';

        parent::__construct($view, $bakery, $labeler);

        $this->beforeFilter('@checkPermissions');
    }

    public function checkPermissions(Route $route, Request $request)
    {
        $user = Overseer::getUser();
        $requested_user_id = $route->getParameter('user');

        if (!$user->hasAccess('users')) {
            if (!in_array($route->getName(), array("{$this->admin_prefix}.user.edit", "{$this->admin_prefix}.user.update")) || $requested_user_id != $user->id) {
                return Redirect::route("{$this->admin_prefix}.user.edit", $user->id)->with(array(
                    'alert_status' => 'warning',
                    'alert'        => trans('clumsy::alerts.user.forbidden'),
                ));
            }
        }
    }

    /**
     * Display a listing of users
     *
     * @return Response
     */
    public function index($data = array())
    {
        $data['items'] = Overseer::findAllUsers();

        $data['title'] = trans('clumsy::titles.users');

        return parent::index();
    }

    public function create($data = array())
    {
        $data['title'] = trans('clumsy::titles.new_user');

        $data['edited_user_id'] = 'new';
        $data['edited_user_group'] = '';

        return $this->edit($id = null, $data);
    }

    /**
     * Store a newly created user in storage.
     *
     * @return Response
     */
    public function store()
    {
        $rules = array_merge(
            $this->model->rules,
            array(
                'password' => 'required|confirmed|min:6|max:255',
            )
        );

        $rules['email'] .= '|unique:users';

        $validator = Validator::make($data = Input::all(), $rules);

        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput()
                ->with(array(
                    'alert_status' => 'warning',
                    'alert'        => trans('clumsy::alerts.invalid'),
                ));
        }

        $new_user = Overseer::register(array(
            'first_name' => Input::get('first_name'),
            'last_name'  => Input::get('last_name'),
            'email'      => Input::get('email'),
            'password'   => Input::get('password'),
        ));

        // Auto-activate
        $new_user->attemptActivation($new_user->getActivationCode());

        $group = Overseer::findGroupByName(Input::get('group'));
        $new_user->addGroup($group);

        return Redirect::route("{$this->admin_prefix}.user.edit", $new_user->id)->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy::alerts.user.added'),
        ));
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return Redirect::route("{$this->admin_prefix}.user.edit", $id);
    }

    public function edit($id, $data = array())
    {
        $data['throttle'] = Overseer::getThrottleProvider();

        if ($id) {
            $data['item'] = Overseer::findUserById($id);
            if ($data['throttle']) {
                $data['item_status'] = Overseer::findThrottlerByUserId($id);
            }

            if ($self = (Overseer::getUser()->id == $id)) {
                $data['suppress_delete'] = true;
            }

            $data['title'] = $self ? trans('clumsy::titles.profile') : trans('clumsy::titles.edit_user');

            $data['edited_user_id'] = $id;
            $data['edited_user_group'] = $data['item']->getGroups()->first()->name;
        }

        $groups = array_map(function ($group) {

            return $group->name;

        }, Overseer::findAllGroups());

        $data['groups'] = array_combine($groups, array_map(function ($group) {

            if (Lang::has('clumsy::fields.roles.'.Str::lower(str_singular($group)))) {
                return trans('clumsy::fields.roles.'.Str::lower(str_singular($group)));
            }

            return str_singular($group);

        }, $groups));

        return parent::edit($id, $data);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $rules = $this->model->rules;

        if ($new_password = (Input::has('new_password') && Input::get('new_password') != '')) {
            $rules['new_password'] = 'required|confirmed|min:6|max:255';
        }

        $validator = Validator::make($data = Input::all(), $rules);

        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput()
                ->with(array(
                    'alert_status' => 'warning',
                    'alert'        => trans('clumsy::alerts.invalid'),
                ));
        }

        if ($new_password) {
            $data['password'] = $data['new_password'];
        }
        unset($data['new_password']);
        unset($data['new_password_confirmation']);

        $user = Overseer::findUserById($id);

        if (Input::has('group')) {
            $groups = Overseer::findAllGroups();

            foreach ($groups as $group) {
                $user->removeGroup($group);
            }

            $group = Overseer::findGroupByName(Input::get('group'));

            $user->addGroup($group);

            unset($data['group']);
        }

        $user->update($data);

        return Redirect::route("{$this->admin_prefix}.user.edit", $user->id)->with(array(
           'alert_status' => 'success',
           'alert'        => trans('clumsy::alerts.user.updated'),
        ));
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        if (Overseer::getUser()->id == $id) {
            $status = 'warning';
            $message = trans('clumsy::alerts.user.suicide');

        } else {
            $user = Overseer::findUserById($id);

            $user->delete();

            $status = 'success';
            $message = trans('clumsy::alerts.user.deleted');
        }

        return Redirect::route("{$this->admin_prefix}.user.index")->with(array(
           'alert_status' => $status,
           'alert'        => $message,
        ));
    }
}
