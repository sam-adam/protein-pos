<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Brand
 *
 * @package App\Models
 */
class Brand extends BaseModel
{
    use SoftDeletes;

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}