<?php

namespace App\Repository;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ProductRepository
 *
 * @package App\Repository
 */
class ProductRepository
{
    /**
     * Find products by query
     *
     * @param string $query
     * @param int    $limit
     * @param int    $offset
     *
     * @return Collection
     */
    public function findByQuery($query, $limit = 5, $offset = 0)
    {
        return Product::with('category', 'brand', 'item')->select('products.*')
            ->where('products.name', 'LIKE', "%{$query}%")
            ->orWhere('products.code', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * Find a product by barcode
     *
     * @param string $barcode
     *
     * @return Product|null
     */
    public function findByBarcode($barcode)
    {
        return Product::whereBarcode($barcode)->first();
    }
}