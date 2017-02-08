<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProductVariantGroup
 *
 * @package App\Models
 */
class ProductVariantGroup extends BaseModel
{
    use SoftDeletes;

    public function items()
    {
        return $this->hasMany(ProductVariantGroupItem::class);
    }
}