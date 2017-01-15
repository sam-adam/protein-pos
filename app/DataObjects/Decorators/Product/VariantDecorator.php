<?php

namespace App\DataObjects\Decorators\Product;

use App\DataObjects\Decorators\Decorator;
use App\DataObjects\Product;
use App\Models\Product as ProductModel;

/**
 * Class VariantDecorator
 *
 * @package App\DataObjects\Decorators\Product
 */
class VariantDecorator implements Decorator
{
    private $product;
    private $productStocks;

    public function __construct(ProductModel $product, $productStocks)
    {
        $this->product       = $product;
        $this->productStocks = $productStocks;
    }

    /** {@inheritDoc} */
    public function decorate(array $attributes)
    {
        if (array_key_exists('product_variant_group_id', $attributes)) {
            unset($attributes['product_variant_group_id']);
        }

        $attributes['all_variants'] = [
            $this->getProductDataObject($this->product)
        ];

        if ($variantGroup = $this->product->variantGroup) {
            if ($variants = $variantGroup->products) {
                $attributes['variants'] = [];

                foreach ($variants as $variant) {
                    if ((int) $variant->id !== (int) $this->product->id) {
                        $attributes['variants'][]     = $this->getProductDataObject($variant);
                        $attributes['all_variants'][] = $this->getProductDataObject($variant);
                    }
                }
            }
        }

        return $attributes;
    }

    private function getProductDataObject(ProductModel $product)
    {
        $productDataObject = new Product($product);
        $stockDecorator    = new StockDecorator($product);
        $stockDecorator->setStockCallback(function (ProductModel $product) {
            return isset($this->productStocks[$product->id])
                ? $this->productStocks[$product->id]
                : 0;
        });

        $productDataObject->addDecorator($stockDecorator);

        return $productDataObject;
    }
}