<?php

namespace App\DataObjects\Decorators\Package;

use App\DataObjects\Decorators\Decorator;
use App\DataObjects\Decorators\Product\StockDecorator;
use App\DataObjects\Decorators\Product\VariantDecorator;
use App\DataObjects\PackageProduct;
use App\Models\Package as PackageModel;
use App\Models\Product;

/**
 * Class WithItemsDecorator
 *
 * @package App\DataObjects\Decorators\Package
 */
class WithItemsDecorator implements Decorator
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
        $attributes['items'] = [];

        foreach ($this->package->items as $packageItem) {
            $packageProduct   = new PackageProduct($packageItem);
            $stockDecorator   = new StockDecorator($packageItem->product);
            $variantDecorator = new VariantDecorator($packageItem->product, $this->productStocks);
            $stockDecorator->setStockCallback(function (Product $product) {
                return isset($this->productStocks[$product->id])
                    ? $this->productStocks[$product->id]
                    : 0;
            });

            $packageProduct->addEagerLoadDecorator('product', $variantDecorator);
            $packageProduct->addEagerLoadDecorator('product', $stockDecorator);

            $attributes['items'][] = $packageProduct;
        }

        return $attributes;
    }
}