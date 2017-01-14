<?php

namespace App\DataObjects;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Interface Serializable
 *
 * @package App\DataObjects
 */
interface Serializable extends \JsonSerializable, Arrayable
{
    /**
     * Do serialization
     *
     * @return array
     */
    public function serializeMember();
}