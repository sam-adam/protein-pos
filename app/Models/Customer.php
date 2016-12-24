<?php

namespace App\Models;

/**
 * Class Customer
 *
 * @package App\Models
 */
class Customer extends BaseModel
{
    public function group()
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}