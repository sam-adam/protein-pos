<?php

namespace App\DataObjects;

use App\Models\Brand as BrandModel;

/**
 * Class Brand
 *
 * @package App\DataObjects
 */
class Brand extends ModelDataObjects
{
    public function __construct(BrandModel $brand)
    {
        $this->setModel($brand);
    }
}