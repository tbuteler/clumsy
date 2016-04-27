<?php

namespace Clumsy\CMS\Policies;

class UserPolicy
{
    public function before($user, $ability)
    {
        if ($ability !== 'destroy') {
            if ($user->isGroupable() && $user->inGroup('Administrator')) {
                return true;
            }
        }
    }

    public function update($user, $model)
    {
        return ($user->id === $model->id);
    }

    public function destroy($user, $model)
    {
        return ($user->id !== $model->id);
    }
}
