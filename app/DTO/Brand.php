<?php

namespace App\DTO;

use App\Models\Brand as BrandModel;

/**
 * Class Brand
 *
 * @package App\DTO
 */
class Brand extends BaseDTO
{
    public $brand;

    public function __construct(BrandModel $brand)
    {
        $this->brand = $brand;
    }
}