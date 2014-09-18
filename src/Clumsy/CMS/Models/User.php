<?php namespace Clumsy\CMS\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class User extends BaseModel {

    public $rules = array(
        'first_name' => 'required|max:255',
        'last_name'  => 'max:255',
        'email'      => 'required|email|max:255',
    );

    public function columns()
    {
        return array(
            'first_name' => trans('clumsy::fields.first_name'),
            'last_name'  => trans('clumsy::fields.last_name'),
            'email'      => trans('clumsy::fields.email'),
        );
    }

}