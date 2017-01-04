<?php

namespace App\DTO;

use App\Models\ProductCategory;

/**
 * Class NonHierarchyCategory
 *
 * @package App\DTO
 */
class NonHierarchyCategory extends BaseDTO
{
    public $category;

    public function __construct(ProductCategory $category)
    {
        $this->category = $category;

        array_push($this->guarded, 'parent_id');
    }
}