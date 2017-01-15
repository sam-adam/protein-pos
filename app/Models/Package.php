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

    public function getActualPrice()
    {
        $actualPrice = 0;

        foreach ($this->items as $item) {
            $actualPrice += ($item->product->price * $item->quantity);
        }

        return $actualPrice;
    }
}