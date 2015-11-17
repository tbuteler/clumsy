<?php

namespace Clumsy\CMS\Models;

use Clumsy\CMS\Facades\Overseer;

class Group extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'clumsy_groups';

    public function users()
    {
        return $this->belongsToMany(Overseer::getUserModel(), 'clumsy_groups_pivot');
    }

    public function addUser($user)
    {
        if (is_object($user)) {
            $user = $user->id;
        }

        $this->users()->attach($user);
    }

    public function removeUser()
    {
        if (is_object($user)) {
            $user = $user->id;
        }

        $this->users()->detach($user);
    }

    public function removeAllUsers()
    {
        $this->users()->detach();
    }
}
