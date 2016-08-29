<?php

namespace Clumsy\CMS\Policies;

use Illuminate\Database\Eloquent\Model as Eloquent;

class UserPolicy
{
    public function before(Eloquent $user, $ability)
    {
        if ($ability !== 'destroy') {
            if ($user->isGroupable() && $user->inGroup('Administrator')) {
                return true;
            }
        }
    }

    public function update(Eloquent $user, Eloquent $model)
    {
        return ($user->id === $model->id);
    }

    public function destroy(Eloquent $user, Eloquent $model)
    {
        return ($user->id !== $model->id);
    }
}
