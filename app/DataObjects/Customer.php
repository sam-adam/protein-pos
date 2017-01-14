<?php

namespace App\DataObjects;

use App\Models\Customer as CustomerModel;

/**
 * Class Customer
 *
 * @package App\DataObjects
 */
class Customer extends ModelDataObjects
{
    protected $eagerLoaded = [
        'group' => [
            'property'    => 'customer_group_id',
            'dataObjects' => CustomerGroup::class
        ]
    ];

    public $customer;

    public function __construct(CustomerModel $customer)
    {
        $this->setModel($customer);
    }
}