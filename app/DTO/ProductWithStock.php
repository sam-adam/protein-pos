<?php

namespace App\DTO;

use App\Models\Product;

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
        'brand'    => [
            'property' => 'brand_id',
            'dto'      => Brand::class
        ]
    ];

    public $product;
    public $actualProduct;
    public $availableStock;
    public $quantity;
    public $canBeSold;
    public $remark;
    public $isPackage;

    public function __construct(Product $product, $availableStock)
    {
        $this->quantity       = $product->isBulkContainer() ? $product->product_item_quantity : 1;
        $this->product        = $product;
        $this->actualProduct  = $product->isBulkContainer() ? $product->item : $product;
        $this->availableStock = $availableStock;
        $this->canBeSold      = $this->availableStock > 0;
        $this->isPackage      = $product->isBulkContainer();

        if ($this->availableStock === 0) {
            $this->remark = 'Out of stock';
        }
    }
}