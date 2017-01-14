<?php

namespace App\DataObjects;

use App\Models\PackageProduct as PackageProductModel;

/**
 * Class PackageProduct
 *
 * @package App\DataObjects
 */
class PackageProduct extends ModelDataObjects
{
    protected $eagerLoaded = [
        'product' => [
            'property'   => 'product_id',
            'dataObject' => Product::class
        ]
    ];

    public function __construct(PackageProductModel $packageModel)
    {
        $this->setModel($packageModel);
        $this->guarded[] = 'package_id';
    }
}