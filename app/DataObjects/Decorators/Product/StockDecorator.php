<?php

namespace App\DataObjects\Decorators\Product;

use App\DataObjects\Decorators\Decorator;
use App\Models\Product as ProductModel;

/**
 * Class StockDecorator
 *
 * @package App\DataObjects\Decorators\Product
 */
class StockDecorator implements Decorator
{
    private $product;
    private $stock;

    public function __construct(ProductModel $product, $stock = 0)
    {
        $this->product = $product;
        $this->stock   = $stock;
    }

    /** {@inheritDoc} */
    public function decorate(array $attributes)
    {
        if (!$this->product->isBulkContainer()) {
            $attributes['stock'] = $this->stock;
        }

        return $attributes;
    }
}