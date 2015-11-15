<?php

namespace Clumsy\CMS\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Clumsy\CMS\Models\Traits\AdminResource;
use Clumsy\CMS\Models\Traits\AdminUser;
use Clumsy\CMS\Models\Traits\Groupable;

class User extends BaseModel implements Authenticatable, Authorizable, CanResetPassword
{
    use AdminResource, AdminUser, Groupable {
        AdminUser::rules insteadof AdminResource;
        AdminUser::displayName insteadof AdminResource;
        AdminUser::displayNamePlural insteadof AdminResource;
    }

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'clumsy_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'group_ids'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
}
