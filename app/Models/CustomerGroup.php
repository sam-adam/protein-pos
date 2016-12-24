<?php

namespace App\Models;

/**
 * Class CustomerGroup
 *
 * @package App\Models
 */
class CustomerGroup extends BaseModel
{
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}