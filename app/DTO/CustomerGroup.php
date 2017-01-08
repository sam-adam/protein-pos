<?php

namespace App\DTO;

use App\Models\CustomerGroup as CustomerGroupModel;

/**
 * Class CustomerGroup
 *
 * @package App\DTO
 */
class CustomerGroup extends BaseDTO
{
    public $group;
    public $groupLabel;

    public function __construct(CustomerGroupModel $group)
    {
        $this->group      = $group;
        $this->groupLabel = $group->name.' ('.$this->group->discount.'% disc.)';
    }
}