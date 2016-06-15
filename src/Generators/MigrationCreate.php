<?php

namespace Clumsy\CMS\Generators;

use Illuminate\Support\Composer;

class MigrationCreate extends Migration
{
    protected $readableName = 'create table migration';

    public function targetName()
    {
        return date('Y_m_d_His').'_create_'.$this->getData('pluralInSnakeCase').'_table';
    }

    public function make()
    {
        $this->setData('classname', 'Create'.$this->getData('objectNamePlural').'Table');

        parent::make();
    }
}
