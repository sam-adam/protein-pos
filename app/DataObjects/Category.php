<?php

namespace App\DataObjects;

use App\Models\ProductCategory;

/**
 * Class NonHierarchyCategory
 *
 * @package App\DataObjects
 */
class Category extends ModelDataObjects
{
    public function __construct(ProductCategory $category)
    {
        $this->setModel($category);
    }
}