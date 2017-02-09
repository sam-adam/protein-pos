<?php

namespace App\DataObjects\DecoratorsVariant;

use App\DataObjects\Decorators\Decorator;
use App\DataObjects\Product;
use App\Models\ProductVariantGroup as ProductVariantGroupModel;

/**
 * Class WithProductsDecorator
 *
 * @package App\DataObjects\DecoratorsVariant
 */
class WithProductsDecorator implements Decorator
{
    protected $variantGroup;

    public function __construct(ProductVariantGroupModel $variantGroup)
    {
        $this->variantGroup = $variantGroup;
    }

    /** {@inheritDoc} */
    public function decorate(array $attributes)
    {
        $attributes['products'] = [];

        foreach ($this->variantGroup->items as $variantGroupItem) {
            array_push($attributes['products'], new Product($variantGroupItem->product));
        }

        return $attributes;
    }
}