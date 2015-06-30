<?php namespace Clumsy\CMS\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Cartalyst\Sentry\Facades\Laravel\Sentry;
use Clumsy\CMS\Facades\Clumsy;

class AuthController extends Controller {

    public function __construct()
    {
        $this->beforeFilter('@loggedInFilter', array('only' => array('login', 'reset', 'doReset')));
    }

    public function loggedInFilter()
    {
        if (Sentry::check())
        {
            return Redirect::to(Clumsy::prefix());
        }
    }

    public function login()
    {
        $data['intended'] = Session::get('intended');
        $data['body_class'] = 'login';

        return View::make('clumsy::auth.login', $data);
    }

    public function postLogin()
    {
        $admin_prefix = Clumsy::prefix();

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

    public function reset()
    {
        $data['body_class'] = 'login';

        return View::make('clumsy::auth.reset', $data);
    }
 
    public function postReset()
    {
        if (!Input::get('email'))
        {
            $alert = trans('clumsy::alerts.auth.login_required');
        }
        else
        {
            try
            {
                $user = Sentry::findUserByLogin(Input::get('email'));

                $resetCode = $user->getResetPasswordCode();

                $url = URL::route('clumsy.do-reset-password', array('user_id' => $user->id, 'code' => $resetCode));

                Mail::send('clumsy::emails.auth.reset', compact('url'), function($message) use($user)
                {
                    $message
                        ->to($user->email, $user->first_name.' '.$user->last_name)
                        ->subject(trans('clumsy::titles.reset-password'));
                });

                if (sizeof(Mail::failures()))
                {
                    $alert = trans('clumsy::alerts.email-error');
                }
                else
                {
                    $alert_status = 'success';
                    $alert = trans('clumsy::alerts.auth.reset-email-sent');
                }
            }
            catch (\Cartalyst\Sentry\Users\UserNotFoundException $e)
            {
                $alert = trans('clumsy::alerts.auth.unknown_user');
            }
        }

        return Redirect::back()->withInput()->with(array(
            'alert_status' => isset($alert_status) ? $alert_status : 'warning',
            'alert' => $alert,
        ));
    }

    public function doReset($user_id, $code)
    {
        $body_class = 'login';

        return View::make('clumsy::auth.do-reset', compact('body_class', 'user_id', 'code'));
    }
 
    public function postDoReset($user_id, $code)
    {
        $validator = Validator::make(Input::all(), array(
            'password' => 'required|confirmed',
        ));

        if ($validator->fails())
        {
            $alert = trans('clumsy::alerts.invalid');
        }
        else
        {
            try
            {
                $user = Sentry::findUserById($user_id);

                if ($user->checkResetPasswordCode($code) && $user->attemptResetPassword($code, Input::get('password')))
                {
                    Sentry::login($user);

                    $admin_prefix = Clumsy::prefix();

                    return Redirect::to(Clumsy::prefix())->with(array(
                        'alert_status' => 'success',
                        'alert'        => trans('clumsy::alerts.auth.password-changed'),
                    ));
                }

                $alert = trans('clumsy::alerts.auth.reset-error');
            }
            catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
            {
                $alert = trans('clumsy::alerts.auth.unknown_user');
            }
        }

        return Redirect::back()->withInput()->withErrors($validator)->with(array(
            'alert_status' => isset($alert_status) ? $alert_status : 'warning',
            'alert'        => $alert,
        ));
    }

    public function logout()
    {
        Sentry::logout();

        return Redirect::route('clumsy.login')->with(array(
            'alert_status' => 'success',
            'alert'        => trans('clumsy::alerts.auth.logged_out')
        ));
    }
}