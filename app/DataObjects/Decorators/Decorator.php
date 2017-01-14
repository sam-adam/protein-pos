<?php

namespace App\DataObjects\Decorators;

/**
 * Interface Decorator
 *
 * @package App\DataObjects\Decorators
 */
interface Decorator
{
    /**
     * Decorate a DataObject
     *
     * @param array $attributes
     *
     * @return array
     */
    public function decorate(array $attributes);
}