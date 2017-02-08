<?php

namespace App\DataObjects;

use App\Models\ProductVariantGroup as ProductVariantGroupModel;

/**
 * Class ProductVariantGroup
 *
 * @package App\DataObjects
 */
class ProductVariantGroup extends ModelDataObjects
{
    public function __construct(ProductVariantGroupModel $productVariantGroup)
    {
        $this->setModel($productVariantGroup);
    }
}