<?php

namespace Clumsy\CMS\Policies;

class BasePolicy
{
    public function before($user, $ability)
    {
        if (!$user->isGroupable() || $user->inGroup('Administrators', 'Editors')) {
            return true;
        }
    }
}