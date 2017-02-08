<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductVariantGroupItem
 *
 * @package App\Models
 */
class ProductVariantGroupItem extends Model
{
    public function group()
    {
        return $this->belongsTo(ProductVariantGroup::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}