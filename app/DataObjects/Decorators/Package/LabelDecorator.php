<?php

namespace App\DataObjects\Decorators\Package;

use App\DataObjects\Decorators\Decorator;
use App\Models\Package as PackageModel;

/**
 * Class LabelDecorator
 *
 * @package App\DataObjects\Decorators\Package
 */
class LabelDecorator implements Decorator
{
    private $package;

    public function __construct(PackageModel $package)
    {
        $this->package = $package;
    }

    /** {@inheritDoc} */
    public function decorate(array $attributes)
    {
        $attributes['label'] = [];

        foreach ($this->package->items as $packageItem) {
            array_push($attributes['label'], $packageItem->quantity.'x '.$packageItem->product->name);
        }

        $attributes['label'] = implode(', ', $attributes['label']);

        return $attributes;
    }
}