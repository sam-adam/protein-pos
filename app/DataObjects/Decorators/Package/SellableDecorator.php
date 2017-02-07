<?php

namespace App\DataObjects\Decorators\Package;

use App\DataObjects\Decorators\Decorator;
use App\Models\Package as PackageModel;

/**
 * Class SellableDecorator
 *
 * @package App\DataObjects\Decorators\Package
 */
class SellableDecorator implements Decorator
{
    private $package;
    private $productStocks;

    public function __construct(PackageModel $package, $productStocks)
    {
        $this->package       = $package;
        $this->productStocks = $productStocks;
    }

    /** {@inheritDoc} */
    public function decorate(array $attributes)
    {
        $attributes['canBeSold'] = $this->package->canBeSold($this->productStocks);

        return $attributes;
    }
}