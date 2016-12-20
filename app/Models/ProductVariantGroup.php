<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProductVariantGroup
 *
 * @package App\Models
 */
class ProductVariantGroup extends BaseModel
{
    use SoftDeletes;

    public function scopeAvailableFor(Builder $query, Product $product)
    {
        return $query->where('id', '<>', $product->product_variant_group_id);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}