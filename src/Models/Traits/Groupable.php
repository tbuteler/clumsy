<?php

namespace Clumsy\CMS\Models\Traits;

use Clumsy\CMS\Models\Group;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Event;

trait Groupable
{
    public static function bootGroupable()
    {
        self::saving(function (Eloquent $model) {
            if (isset($model->group_ids)) {
                $groups = array_filter((array)$model->group_ids);
                unset($model->group_ids);
                Event::listen('clumsy.saved: User', function (Eloquent $user) use ($groups) {
                    $user->groups()->sync($groups);
                }, 100);
            }
        });
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'clumsy_groups_pivot');
    }

    public function addToGroup()
    {
        foreach (func_get_args() as $groupName) {

            if ($groupName === 'User') {
                continue;
            }

            $group = Group::where('name', $groupName)->firstOrFail();
            $this->groups()->attach($group->id);
        }
    }

    public function removeFromGroup()
    {
        foreach (func_get_args() as $groupName) {

            if ($groupName === 'User') {
                continue;
            }

            $group = Group::where('name', $groupName)->firstOrFail();
            $this->groups()->detach($group->id);
        }
    }

    public function removeFromAllGroups()
    {
        $this->groups()->detach();
    }

    public function demoteToUser()
    {
        $this->removeFromAllGroups();
    }

    public function inGroup()
    {
        $groups = $this->groups->pluck('name')->toArray();

        foreach (func_get_args() as $groupName) {

            if (in_array($groupName, $groups) || (empty($groups) && $groupName === 'User')) {
                return true;
            }
        }

        return false;
    }

    public function getGroupIds()
    {
        return $this->groups->pluck('id')->toArray();
    }
}
