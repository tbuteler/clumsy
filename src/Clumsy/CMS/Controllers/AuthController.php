<?php namespace Clumsy\CMS\Controllers;

use View;
use Input;
use Sentry;
use Redirect;

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
            
            return Redirect::intended('admin.home');

        } catch (\Cartalyst\Sentry\Users\LoginRequiredException $e) {
            
            $message = 'Login field is required.';

        } catch (\Cartalyst\Sentry\Users\PasswordRequiredException $e) {
            
            $message = 'Password field is required.';

        } catch (\Cartalyst\Sentry\Users\WrongPasswordException $e) {
            
            $message = 'Wrong password, try again.';

        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {
            
            $message = 'User was not found.';

        } catch (\Cartalyst\Sentry\Users\UserNotActivatedException $e) {
            
            $message = 'User is not activated.';
        }

        return Redirect::back()->withInput()->with(array(
            'status'    => 'warning',
            'message'   => $message,
        ));
    }

    public function logout()
    {
        Sentry::logout();

        return Redirect::route('login')->with(array(
            'status'  => 'success',
            'message' => 'You have logged out.'
        ));
    }
}