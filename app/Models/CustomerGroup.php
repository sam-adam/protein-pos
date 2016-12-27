<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CustomerGroup
 *
 * @package App\Models
 */
class CustomerGroup extends BaseModel
{
    use SoftDeletes;

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}