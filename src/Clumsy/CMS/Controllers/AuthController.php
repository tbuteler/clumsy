<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Cartalyst\Sentry\Facades\Laravel\Sentry;

class AuthController extends \BaseController {

    public function login()
    {
        $admin_prefix = Config::get('clumsy::admin_prefix');

        if (Sentry::check())
        {
            return Redirect::to($admin_prefix);
        }

        $data['admin_prefix'] = $admin_prefix;
        $data['intended'] = Session::get('intended');
        $data['body_class'] = 'login';

        return View::make('clumsy::login', $data);
    }

    public function postLogin()
    {
        $admin_prefix = Config::get('clumsy::admin_prefix');

        try
        {
            $credentials = array(
                'email'    => Input::get('email'),
                'password' => Input::get('password'),
            );

            $user = Sentry::authenticate($credentials, Input::has('remember'));

            return Redirect::intended(URL::route("$admin_prefix.home"));
        }

        catch (\Cartalyst\Sentry\Users\LoginRequiredException $e)
        {
            $alert = trans('clumsy::alerts.auth.login_required');
        }

        catch (\Cartalyst\Sentry\Users\PasswordRequiredException $e)
        {
            $alert = trans('clumsy::alerts.auth.password_required');
        }

        catch (\Cartalyst\Sentry\Users\WrongPasswordException $e)
        {
            $alert = trans('clumsy::alerts.auth.wrong_password');
        }
        catch (\Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            $alert = trans('clumsy::alerts.auth.unknown_user');
        }

        catch (\Cartalyst\Sentry\Users\UserNotActivatedException $e)
        {
            $alert = trans('clumsy::alerts.auth.inactive_user');
        }

        catch (\Cartalyst\Sentry\Throttling\UserSuspendedException $e)
        {
            $alert = trans('clumsy::alerts.auth.suspended_user');
        }

        catch (\Cartalyst\Sentry\Throttling\UserBannedException $e)
        {
            $alert = trans('clumsy::alerts.auth.banned_user');
            $alert_status = 'danger';
        }

        return Redirect::back()->withInput()->with(array(
            'alert_status' => isset($alert_status) ? $alert_status : 'warning',
            'alert'        => $alert,
        ));
    }

    public function logout()
    {
        Sentry::logout();

        return Redirect::route('login')->with(array(
            'alert_status' => 'success',
            'alert'        => trans('clumsy::alerts.auth.logged_out')
        ));
    }
}