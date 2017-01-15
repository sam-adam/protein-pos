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

    public function __construct(ProductModel $product)
    {
        $this->product = $product;
    }

    /** {@inheritDoc} */
    public function decorate(array $attributes)
    {
        if (array_key_exists('product_variant_group_id', $attributes)) {
            unset($attributes['product_variant_group_id']);
        }

        if ($variantGroup = $this->product->variantGroup) {
            if ($variants = $variantGroup->products) {
                $attributes['variants'] = [];

                foreach ($variants as $variant) {
                    if ((int) $variant->id !== (int) $this->product->id) {
                        $productDataObject = new Product($variant);
                        $productDataObject->addDecorator(new StockDecorator($variant));

                        $attributes['variants'][] = $productDataObject;
                    }
                }
            }
        }

        return $attributes;
    }
}