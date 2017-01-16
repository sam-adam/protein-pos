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

    public function canBeSold($stocks)
    {
        $insufficientStockProducts = [];

        foreach ($this->items as $packageItem) {
            if (data_get($stocks, $packageItem->product_id, 0) < $packageItem->quantity) {
                $insufficientStockProducts[$packageItem->product_id] = $packageItem;
            }
        }

        if (count($insufficientStockProducts) > 0 && $this->is_customizable) {
            $hasVariants = [];

            foreach ($insufficientStockProducts as $productId => $packageItem) {
                $product = $packageItem->product;

                if ($product->variantGroup && $product->variantGroup->products) {
                    foreach ($product->variantGroup->products as $variant) {
                        if (data_get($stocks, $variant->id, 0) >= $packageItem->quantity) {
                            $hasVariants[$productId] = $variant;
                        }
                    }
                }
            }

            foreach ($hasVariants as $productId => $variant) {
                if (isset($insufficientStockProducts[$productId])) {
                    unset($insufficientStockProducts[$productId]);
                }
            }
        }

        return count($insufficientStockProducts) === 0;
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