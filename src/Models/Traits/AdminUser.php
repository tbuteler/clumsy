<?php

namespace Clumsy\CMS\Models\Traits;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;

trait AdminUser
{
    use Authenticatable, Authorizable, CanResetPassword;

    public function rules()
    {
        return [
            'name'     => 'required|max:255',
            'email'    => 'required|email|max:255',
            'password' => 'required|confirmed|min:6|max:255',
        ];
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
        return $this->isGroupable() ? str_singular($this->groups->implode('name', ', ')) : null;
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
