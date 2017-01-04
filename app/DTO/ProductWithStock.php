<?php

namespace App\DTO;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductWithStock
 *
 * @package App\DTO
 */
class ProductWithStock extends BaseDTO
{
    protected $eagerLoaded = [
        'category' => [
            'property' => 'product_category_id',
            'dto'      => NonHierarchyCategory::class
        ],
        'brand' => [
            'property' => 'brand_id',
            'dto'      => Brand::class
        ]
    ];

    public $product;
    public $availableStock;

    public function __construct(Product $product, $availableStock)
    {
        $this->product        = $product;
        $this->availableStock = $availableStock;
    }
}