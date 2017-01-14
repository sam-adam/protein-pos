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
    public $groupLabel;

    public function __construct(CustomerGroupModel $group)
    {
        $this->setModel($group);
    }
}