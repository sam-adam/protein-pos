<?php

namespace App\Repository;

use App\Models\Product;
use App\Models\ProductVariantGroup;
use Illuminate\Support\Collection;

/**
 * Class ProductVariantRepository
 *
 * @package App\Repository
 */
class ProductVariantRepository
{
    /**
     * Find variant groups of a product
     *
     * @param Product $product
     *
     * @return Collection
     */
    public function findGroupByProduct(Product $product)
    {
        return ProductVariantGroup::select('product_variant_groups.*')
            ->join('product_variant_group_items', 'product_variant_group_items.product_variant_group_id', '=', 'product_variant_groups.id')
            ->where('product_variant_group_items.product_id', '=', $product->id)
            ->groupBy('product_variant_groups.id')
            ->get();
    }
}