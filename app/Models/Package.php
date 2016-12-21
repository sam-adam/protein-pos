<?php

namespace App\Models;

/**
 * Class Package
 *
 * @package App\Models
 */
class Package extends BaseModel
{
    public function items()
    {
        return $this->hasMany(PackageProduct::class);
    }
}