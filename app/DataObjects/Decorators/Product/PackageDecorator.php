<?php

namespace App\DataObjects\Decorators\Product;

use App\DataObjects\Decorators\Decorator;
use App\DataObjects\Package;
use App\Models\Package as PackageModel;
use App\Models\Product as ProductModel;
use Illuminate\Support\Collection;

/**
 * Class PackageDecorator
 *
 * @package App\DataObjects\Decorators\Product
 */
class PackageDecorator implements Decorator
{
    private $product;
    private $packages;

    public function __construct(ProductModel $product, Collection $packages = null)
    {
        $this->product  = $product;
        $this->packages = $packages;
    }

    /** {@inheritDoc} */
    public function decorate(array $attributes)
    {
        if ($this->packages && $this->packages->count() > 0) {
            $attributes['inPackages'] = [];

            /** @var PackageModel $package */
            foreach ($this->packages as $package) {
                array_push($attributes['inPackages'], new Package($package));
            }
        }

        return $attributes;
    }
}