<?php

namespace App\DataObjects;

use App\Models\Product as ProductModel;

/**
 * Class Product
 *
 * @package App\DataObjects
 */
class Product extends ModelDataObjects
{
    protected $eagerLoaded = [
        'category' => [
            'property'   => 'product_category_id',
            'dataObject' => Category::class
        ],
        'brand'    => [
            'property'   => 'brand_id',
            'dataObject' => Brand::class
        ]
    ];

    public function __construct(ProductModel $product)
    {
        $this->setModel($product);
    }
}