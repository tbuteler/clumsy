<?php

namespace Clumsy\CMS\Models\Traits;

use Clumsy\CMS\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

trait AdminUser
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable;

    /**
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getDates()
    {
        $defaults = [static::CREATED_AT, static::UPDATED_AT, 'last_login'];

        return $this->timestamps ? array_merge($this->dates, $defaults) : $this->dates;
    }

    public function rules()
    {
        return [
            'name' => 'required|max:191',
            'email' => 'required|email|max:191|unique:'.$this->getTable().',email,'.$this->getKey(),
            'password' => 'required|confirmed|min:6|max:191',
            'new_password' => 'confirmed|min:6|max:191',
        ];
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function isSuperAdmin()
    {
        return $this->is_super_admin;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function getUsernameAttribute()
    {
        return $this->name ?: $this->email;
    }

    public function getUsergroupAttribute()
    {
        if ($this->isGroupable()) {
            $usergroup = str_singular($this->groups->implode('name', ', '));
            $usergroupLower = Str::lower($usergroup);
            if (trans()->has("clumsy::fields.roles.{$usergroupLower}")) {
                return trans("clumsy::fields.roles.{$usergroupLower}");
            }

            return $usergroup;
        }

        return null;
    }

    public function nameArray()
    {
        $nameArray = [
            'firstName'  => '',
            'middleName' => '',
            'lastName'   => '',
        ];

        $name = preg_split('/\s+/', trim($this->name));

        $nameArray['firstName'] = array_shift($name);
        $nameArray['lastName'] = array_pop($name);
        if (count($name)) {
            $nameArray['middleName'] = implode(' ', $name);
        }

        return $nameArray;
    }

    public function getFirstNameAttribute()
    {
        return array_get($this->nameArray(), 'firstName');
    }

    public function getMiddleNameAttribute()
    {
        return array_get($this->nameArray(), 'middleName');
    }

    public function getLastNameAttribute()
    {
        return array_get($this->nameArray(), 'lastName');
    }

    public function isGroupable()
    {
        return in_array(
            Groupable::class, class_uses_recursive(get_class($this))
        );
    }

    public function displayName()
    {
        return trans('clumsy::titles.user');
    }

    public function displayNamePlural()
    {
        return trans('clumsy::titles.users');
    }
}
