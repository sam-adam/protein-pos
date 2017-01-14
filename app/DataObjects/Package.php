<?php

namespace App\DataObjects;

use App\Models\Package as PackageModel;

/**
 * Class Package
 *
 * @package App\DataObjects
 */
class Package extends ModelDataObjects
{
    public function __construct(PackageModel $package)
    {
        $this->setModel($package);
    }
}