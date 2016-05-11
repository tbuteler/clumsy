<?php

namespace Clumsy\CMS\Auth;

use Illuminate\Auth\AuthManager;
use Illuminate\Auth\Access\Gate;
use Illuminate\Auth\Passwords\DatabaseTokenRepository as DbRepository;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;
use Clumsy\CMS\Models\BaseModel;
use Clumsy\CMS\Models\Group;
use Clumsy\CMS\Policies\UserPolicy;
use Clumsy\CMS\Policies\BasePolicy;

class Overseer
{
    protected $auth;
    protected $gate;

    protected $userModel;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->userModel = $this->app['config']['clumsy.cms.authentication-model'];

        $this->auth = new AuthManager($app);
        $this->auth->shouldUse('clumsy');
        $this->app['config']['auth.guards.clumsy'] = [
            'driver'   => 'session',
            'provider' => 'clumsy',
        ];
        $this->app['config']['auth.providers.clumsy'] = [
            'driver' => 'eloquent',
            'model'  => $this->userModel,
        ];

        $this->bindGate();
        $this->bindPasswordBroker();
        $this->bindTokenRepository();

        $app['request']->setUserResolver(function () use ($app) {
            return $app['clumsy.auth']->user();
        });
    }

    protected function beforeAuthorize($user, $ability)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }

    protected function bindGate()
    {
        $this->gate = new Gate(
            $this->app,
            function () {
                    return $this->app['clumsy.auth']->user();
            },
            [],
            [
                $this->userModel => UserPolicy::class,
                BaseModel::class => BasePolicy::class,
            ],
            [
                function ($user, $ability) {
                    return $this->beforeAuthorize($user, $ability);
                }
            ]
        );

        $this->app->instance(GateContract::class, $this->gate);
    }

    protected function bindPasswordBroker()
    {
        $this->app->singleton('clumsy.password', function ($app) {

            $tokens = $app['clumsy.password.tokens'];

            $users = $app['clumsy.auth']->getProvider();

            $view = 'clumsy::emails.auth.reset';

            return new PasswordBroker(
                $tokens, $users, $app['mailer'], $view
            );
        });
    }

    protected function bindTokenRepository()
    {
        $this->app->singleton('clumsy.password.tokens', function ($app) {

            $connection = $app['db']->connection();

            $table = 'clumsy_password_resets';

            $key = $app['config']['app.key'];

            $expire = $app['config']->get('clumsy.cms.password-reset-expiration', 60);

            return new DbRepository($connection, $table, $key, $expire);
        });
    }

    public function auth()
    {
        return $this->auth;
    }

    public function gate()
    {
        return $this->gate;
    }

    public function password()
    {
        return $this->app['clumsy.password'];
    }

    public function getUserModel()
    {
        return $this->userModel;
    }

    public function register(array $user)
    {
        array_set($user, 'password', array_get($user, 'password', str_random(9)));
        return with(new $this->userModel)->create($user);
    }

    public function getAvailableGroups()
    {
        $groups = [null => 'User'] + Group::pluck('name', 'id')->toArray();
        return array_map(function ($group) {
            $groupLower = Str::lower(str_singular($group));
            if (trans()->has("clumsy::fields.roles.{$groupLower}")) {
                return trans("clumsy::fields.roles.{$groupLower}");
            }
            return $group;
        }, $groups);
    }

    public function canManageUsers()
    {
        return $this->can('update', new $this->userModel);
    }

    public function can()
    {
        return call_user_func_array([$this->gate, 'check'], func_get_args());
    }

    public function cannot()
    {
        return call_user_func_array([$this->gate, 'denies'], func_get_args());
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->auth, $name], $arguments);
    }
}
