<?php

namespace Clumsy\CMS\Panels;

use Clumsy\CMS\Panels\Traits\Editable;

class Edit extends Panel
{
    use Editable;

    protected $action = 'edit';

    protected $inheritable = true;
}
