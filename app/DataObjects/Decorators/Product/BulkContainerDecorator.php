<?php

namespace App\DataObjects\Decorators\Product;

use App\DataObjects\Decorators\Decorator;
use App\DataObjects\Product;
use App\Models\Product as ProductModel;

/**
 * Class BulkContainerDecorator
 *
 * @package App\DataObjects\Decorators\Product
 */
class BulkContainerDecorator implements Decorator
{
    private $product;

    public function __construct(ProductModel $product)
    {
        $this->product = $product;
    }

    /** {@inheritDoc} */
    public function decorate(array $attributes)
    {
        if (array_key_exists('product_item_id', $attributes)) {
            unset($attributes['product_item_id']);
        }

        if (array_key_exists('product_item_quantity', $attributes)) {
            unset($attributes['product_item_quantity']);
        }

        if ($this->product->isBulkContainer()) {
            $attributes['isBulkContainer'] = true;
            $attributes['item']            = new Product($this->product->item());
            $attributes['quantity']        = $this->product->product_item_quantity;
        } else {
            $attributes['quantity']        = 1;
        }

        return $attributes;
    }
}