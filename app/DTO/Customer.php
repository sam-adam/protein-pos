<?php

namespace App\DTO;

use App\Models\Customer as CustomerModel;

/**
 * Class Customer
 *
 * @package App\DTO
 */
class Customer extends BaseDTO
{
    protected $eagerLoaded = [
        'group' => [
            'property' => 'customer_group_id',
            'dto'      => CustomerGroup::class
        ]
    ];

    public $customer;
    public $isInGroup;

    public function __construct(CustomerModel $customer)
    {
        $this->customer  = $customer;
        $this->isInGroup = $customer->customer_group_id !== null;
    }
}