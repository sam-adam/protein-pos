<?php

namespace App\DataObjects;

use App\Models\PackageVariant as PackageVariantModel;

/**
 * Class PackageVariant
 *
 * @package App\DataObjects
 */
class PackageVariant extends ModelDataObjects
{
    protected $eagerLoaded = [
        'variant' => [
            'property'   => 'product_variant_group_id',
            'dataObject' => ProductVariantGroup::class
        ]
    ];

    public function __construct(PackageVariantModel $packageModel)
    {
        $this->setModel($packageModel);
        $this->guarded[] = 'package_id';
    }
}