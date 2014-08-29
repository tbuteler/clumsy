<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Cartalyst\Sentry\Facades\Laravel\Sentry;

class AuthController extends \BaseController {

    public function login()
    {
        if (Sentry::check())
        {
            return Redirect::route('admin.home');
        }

        return View::make('clumsy/cms::admin.login', array('title' => 'Login', 'body_class' => 'login'));
    }

    public function postLogin()
    {
        try
        {
            $credentials = array(
                'email'    => Input::get('email'),
                'password' => Input::get('password'),
            );

            $user = Sentry::authenticate($credentials, Input::has('remember'));
            
            return Redirect::intended(URL::route('admin.home'));

        } catch (\Cartalyst\Sentry\Users\LoginRequiredException $e) {
            
            $alert = trans('clumsy/cms::alerts.auth.login_required');

        } catch (\Cartalyst\Sentry\Users\PasswordRequiredException $e) {
            
            $alert = trans('clumsy/cms::alerts.auth.password_required');

        } catch (\Cartalyst\Sentry\Users\WrongPasswordException $e) {
            
            $alert = trans('clumsy/cms::alerts.auth.wrong_password');

        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {
            
            $alert = trans('clumsy/cms::alerts.auth.unknown_user');

        } catch (\Cartalyst\Sentry\Users\UserNotActivatedException $e) {
            
            $alert = trans('clumsy/cms::alerts.auth.inactive_user');
        }

        return Redirect::back()->withInput()->with(array(
            'alert_status' => 'warning',
            'alert'        => $alert,
        ));
    }

    public function logout()
    {
        Sentry::logout();

        return Redirect::route('login')->with(array(
            'alert_status' => 'success',
            'alert'        => trans('clumsy/cms::alerts.auth.logged_out')
        ));
    }
}