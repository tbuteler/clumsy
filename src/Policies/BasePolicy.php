<?php

namespace Clumsy\CMS\Policies;

class BasePolicy
{
    public function before($user)
    {
        return (!$user->isGroupable() || $user->inGroup('Administrator', 'Editor'));
    }
}
