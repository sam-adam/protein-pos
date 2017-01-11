<?php

namespace App\Exceptions;

use App\Models\Product;

/**
 * Class InsufficientStockException
 *
 * @package App\Exceptions
 */
class InsufficientStockException extends \LogicException
{
    /** @var Product */
    protected $product;
    /** @var int */
    protected $requestedQuantity;

    public function __construct(Product $product, $requestedQuantity)
    {
        $this->product           = $product;
        $this->requestedQuantity = $requestedQuantity;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getRequestedQuantity()
    {
        return $this->requestedQuantity;
    }
}