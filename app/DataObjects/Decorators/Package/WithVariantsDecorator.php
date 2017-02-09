<?php

namespace App\DataObjects\Decorators\Package;

use App\DataObjects\Decorators\Decorator;
use App\DataObjects\DecoratorsVariant\WithProductsDecorator;
use App\DataObjects\PackageVariant;
use App\Models\Package as PackageModel;

/**
 * Class WithVariantsDecorator
 *
 * @package App\DataObjects\Decorators\Package
 */
class WithVariantsDecorator implements Decorator
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
        $attributes['variants'] = [];

        foreach ($this->package->variants as $packageVariant) {
            $variant = new PackageVariant($packageVariant);
            $variant->addEagerLoadDecorator('variant', new WithProductsDecorator($packageVariant->variant, $this->productStocks));

            $attributes['variants'][] = $variant;
        }

        return $attributes;
    }
}