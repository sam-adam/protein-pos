<?php

namespace App\DataObjects;

use App\Models\CustomerGroup as CustomerGroupModel;

/**
 * Class CustomerGroup
 *
 * @package App\DataObjects
 */
class CustomerGroup extends ModelDataObjects
{
    public $group;

    public function __construct(CustomerGroupModel $group)
    {
        $this->setModel($group);
    }

    /** {@inheritDoc} */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'label' => $this->model->name.' ('.$this->model->discount.'% discount)'
        ]);
    }
}