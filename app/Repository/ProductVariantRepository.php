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
     * Find variants by query
     *
     * @param string $query
     * @param int    $limit
     * @param int    $offset
     *
     * @return Collection
     */
    public function findByQuery($query, $limit = 5, $offset = 0)
    {
        return ProductVariantGroup::select('product_variant_groups.*')
            ->where('product_variant_groups.name', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

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