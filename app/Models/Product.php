<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /** {@inheritDoc} */
    protected static function boot()
    {
        parent::boot();

        self::saved(function (Product $product) {
            foreach ($product->containers as $container) {
                $container->brand_id            = $product->brand_id;
                $container->product_category_id = $product->product_category_id;
                $container->saveOrFail();
            }
        });

        self::deleted(function (Product $product) {
            foreach ($product->containers as $container) {
                $container->delete();
            }
        });
    }

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

    public function item()
    {
        return $this->hasOne(Product::class, 'id', 'product_item_id');
    }

    public function containers()
    {
        return $this->hasMany(Product::class, 'product_item_id', 'id');
    }

    public function scopeNonContainer(Builder $query)
    {
        return $query->whereNull('product_item_id');
    }

    public function isBulkContainer()
    {
        return $this->product_item_id !== null;
    }
}