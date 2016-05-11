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
                    'groups' => Overseer::getAvailableGroups(),
                ]);
    }

    public function edit($id)
    {
        $this->loadPanel();

        if (Overseer::user()->id == $id) {
            $this->panel->suppressDelete = true;
        }

        return parent::edit($id);
    }

    public function triggerUpdate($item, $data)
    {
        if (isset($data['new_password'])) {
            $data['password'] = $data['new_password'];
        }

        return parent::triggerUpdate($item, $data);
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
