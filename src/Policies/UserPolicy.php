<?php

namespace Clumsy\CMS\Policies;

class UserPolicy
{
    public function before($user, $ability)
    {
        if ($ability !== 'destroy') {

            if ($user->isGroupable() && $user->inGroup('Administrators')) {
                return true;
            }
        }
    }

    public function update($user, $model)
    {
        if ($user->id === $model->id) {
            return true;
        }
    }

    public function destroy($user, $model)
    {
        if ($user->id !== $model->id) {
            return true;
        }
    }
}