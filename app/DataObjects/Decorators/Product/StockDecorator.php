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
    private $stockCallback;

    public function __construct(ProductModel $product, $stock = 0)
    {
        $this->product = $product;
        $this->stock   = $stock;
    }

    /**
     * Set stock finder callback
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setStockCallback(callable $callback)
    {
        $this->stockCallback = $callback;

        return $this;
    }

    /** {@inheritDoc} */
    public function decorate(array $attributes)
    {
        $attributes['stock'] = $this->stockCallback
            ? call_user_func($this->stockCallback, $this->product)
            : $this->stock;

        return $attributes;
    }
}