<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Product
 *
 * @package App\Models
 */
class Product extends BaseModel
{
    use SoftDeletes;

    protected $casts = [
        'is_service' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variantGroup()
    {
        return $this->belongsTo(ProductVariantGroup::class, 'product_variant_group_id');
    }
}