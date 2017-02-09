<?php

namespace App\DataObjects\DecoratorsVariant;

use App\DataObjects\Decorators\Decorator;
use App\DataObjects\Decorators\Product\StockDecorator;
use App\DataObjects\Product;
use App\Models\Product as ProductModel;
use App\Models\ProductVariantGroup as ProductVariantGroupModel;

/**
 * Class WithProductsDecorator
 *
 * @package App\DataObjects\DecoratorsVariant
 */
class WithProductsDecorator implements Decorator
{
    private $variantGroup;
    private $productStocks;

    public function __construct(ProductVariantGroupModel $variantGroup, $productStocks)
    {
        $this->variantGroup  = $variantGroup;
        $this->productStocks = $productStocks;
    }

    /** {@inheritDoc} */
    public function decorate(array $attributes)
    {
        $attributes['products'] = [];

        foreach ($this->variantGroup->items as $variantGroupItem) {
            $productDataObject = new Product($variantGroupItem->product);
            $stockDecorator    = new StockDecorator($variantGroupItem->product);
            $stockDecorator->setStockCallback(function (ProductModel $product) {
                return isset($this->productStocks[$product->id])
                    ? $this->productStocks[$product->id]
                    : 0;
            });

            $productDataObject->addDecorator($stockDecorator);

            array_push($attributes['products'], $productDataObject);
        }

        return $attributes;
    }
}