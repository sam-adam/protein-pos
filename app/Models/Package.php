<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Package
 *
 * @package App\Models
 */
class Package extends BaseModel
{
    use SoftDeletes;

    public function items()
    {
        return $this->hasMany(PackageProduct::class);
    }
}