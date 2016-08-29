<?php

namespace Clumsy\CMS\Generators;

class MigrationPivot extends Migration
{
    protected $readableName = 'pivot table migration';

    public function targetName()
    {
        $pivot = $this->getData('a.nameInSnakeCase').'_'.$this->getData('b.nameInSnakeCase');
        return date('Y_m_d_His').'_create_'.$pivot.'_table';
    }

    public function make()
    {
        $this->setData('classname', 'Create'.$this->getData('objectNamePlural').'Table');

        parent::make();
    }
}
