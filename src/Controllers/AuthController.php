<?php

namespace Clumsy\CMS\Controllers;

use Carbon\Carbon;
use Clumsy\CMS\Facades\Overseer;
use Clumsy\CMS\Facades\Clumsy;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    use AuthenticatesUsers, ValidatesRequests;

    protected $username;

    protected $routePrefix;
    protected $loginPath;
    protected $redirectPath;
    protected $redirectAfterLogout;

    protected $maxLoginAttempts = INF;
    protected $lockoutTime;

    public function __construct()
    {
        $this->routePrefix = config('clumsy.cms.authentication-prefix');

        $this->username = config('clumsy.cms.authentication-attribute');

        $this->loginPath = "{$this->routePrefix}/login";
        $this->redirectAfterLogout = $this->loginPath;

        if ($this->throttles()) {
            $this->maxLoginAttempts = config('clumsy.cms.throttling-max-attempts');
            $this->lockoutTime = config('clumsy.cms.throttling-lockout-time');
        }
    }

    protected function throttles()
    {
        return config('clumsy.cms.authentication-throttling');
    }

    public function redirectPath()
    {
        return Clumsy::prefix();
    }

    /**
     * Check if the current user is logged in
     *
     * @return string
     */
    public function isLoggedIn()
    {
        if (Overseer::check()) {
            return 'user';
        }

        return 'guest';
    }

    /**
     * Show the application login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogin()
    {
        if (Overseer::check()) {
            return redirect($this->redirectPath());
        }

        $data['bodyClass'] = 'login';

        return view('clumsy::auth.login', $data);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required', 'password' => 'required',
        ], trans('clumsy::alerts.auth.validate'));

        $throttles = $this->throttles();

        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->credentials($request);
        if (Overseer::attempt($credentials, $request->has('remember'))) {
            return $this->sendLoginResponse($request);
        }

        if ($throttles) {
            $this->incrementLoginAttempts($request);
        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withAlert([
                'warning' => trans('clumsy::alerts.auth.failed'),
            ]);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $user->last_login = Carbon::now();
        $user->save();
    }

    /**
     * Get the login lockout error message.
     *
     * @param  int  $seconds
     * @return string
     */
    protected function getLockoutErrorMessage($seconds)
    {
        return trans('clumsy::alerts.auth.lockout', ['seconds' => $seconds]);
    }

    public function reset()
    {
        if (Overseer::check()) {
            return redirect($this->redirectPath());
        }

        $data['bodyClass'] = 'login';

        return view('clumsy::auth.reset', $data);
    }

    public function postReset(Request $request)
    {
        if (!request()->get('email')) {
            $alert = trans('clumsy::alerts.auth.login_required');

        } else {

            $response = Overseer::password()->sendResetLink($request->only('email'), function (Message $message) {
                $message->subject(trans('clumsy::titles.reset-password'));
            });

            switch ($response) {
                case 'passwords.sent':
                    if (count(Mail::failures())) {
                        $alert = trans('clumsy::alerts.email-error');
                    } else {
                        $alertStatus = 'success';
                        $alert = trans('clumsy::alerts.auth.reset-email-sent');
                    }
                    break;

                case 'passwords.user':
                    $alert = trans('clumsy::alerts.auth.unknown-user');
                    break;
            }
        }

        return back()->withInput()->withAlert([
            isset($alertStatus) ? $alertStatus : 'warning' => $alert,
        ]);
    }

    public function doReset($token)
    {
        if (Overseer::check()) {
            return redirect($this->redirectPath());
        }

        $bodyClass = 'login';

        return view('clumsy::auth.do-reset', compact('bodyClass', 'token'));
    }

    public function postDoReset($token, Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:6|max:255',
        ]);

        $credentials = array_merge(compact('token'), $request->only(
            'email', 'password', 'password_confirmation'
        ));

        $response = Overseer::password()->reset($credentials, function ($user, $password) {
            $user->password = $password;
            $user->save();
            Overseer::login($user);
        });

        switch ($response) {
            case 'passwords.reset':
                return redirect($this->redirectPath())->withAlert([
                    'success' => trans('clumsy::alerts.auth.password-changed'),
                ]);

            case 'passwords.user':
                $alert = trans('clumsy::alerts.auth.unknown-user');
                break;

            default:
                $alert = trans('clumsy::alerts.auth.reset-error');
        }

        return back()->withInput($request->only('email'))->withAlert([
            'warning' => $alert,
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        Overseer::logout();

        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/')->withAlert([
            'success' => trans('clumsy::alerts.auth.logged-out')
        ]);
    }
}
