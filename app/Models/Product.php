<?php

namespace App\Models;

/**
 * Class Product
 *
 * @package App\Models
 */
class Product extends BaseModel
{
    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}