<?php

namespace Clumsy\CMS\Controllers;

use Clumsy\CMS\Facades\Overseer;
use Clumsy\CMS\Controllers\AdminController;

class UsersController extends AdminController
{
    public function modelClass($resource = null)
    {
        return Overseer::getUserModel();
    }

    public function loadPanel($action = null)
    {
        parent::loadPanel($action);

        $this->panel
                ->columns([
                    'name'  => trans('clumsy::fields.name'),
                    'email' => trans('clumsy::fields.email'),
                ])
                ->setData([
                    'groups' => array_map('str_singular', Overseer::getAvailableGroups()),
                ]);
    }

    public function destroy($id)
    {
        if (Overseer::user()->id == $id) {
            return back()->withAlert([
               'warning' => trans('clumsy::alerts.user.suicide'),
            ]);
        }

        return parent::destroy($id);
    }
}
