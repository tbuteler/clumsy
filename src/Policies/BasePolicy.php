<?php

namespace Clumsy\CMS\Policies;

use Illuminate\Database\Eloquent\Model as Eloquent;

class BasePolicy
{
    public function before(Eloquent $user)
    {
        return (!$user->isGroupable() || $user->inGroup('Administrator', 'Editor'));
    }
}
