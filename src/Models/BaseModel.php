<?php

namespace Clumsy\CMS\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Clumsy\CMS\Models\Traits\AdminResource;

class BaseModel extends Eloquent
{
    use AdminResource;

    protected $guarded = ['id'];
}
