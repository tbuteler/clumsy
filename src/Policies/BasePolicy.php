<?php

namespace Clumsy\CMS\Policies;

class BasePolicy
{
    public function before($user, $ability)
    {
        return (!$user->isGroupable() || $user->inGroup('Administrators', 'Editors'));
    }
}
