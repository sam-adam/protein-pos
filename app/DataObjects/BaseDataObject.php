<?php

namespace App\DataObjects;

use App\DataObjects\Decorators\Decorator;
use Illuminate\Support\Str;

/**
 * Class BaseDataObject
 *
 * @package App\DataObjects
 */
abstract class BaseDataObject implements Serializable
{
    /** @var Decorator[] */
    protected $decorators = [];
    /** @var array */
    protected $attributes = [];

    /**
     * Add a decorator
     *
     * @param Decorator $decorator
     *
     * @return $this
     */
    public function addDecorator(Decorator $decorator)
    {
        array_push($this->decorators, $decorator);

        return $this;
    }

    /** {@inheritDoc} */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /** {@inheritDoc} */
    public function toArray()
    {
        $serialized = $this->serializeMember();
        $serialized = $this->applyDecorators($serialized);
        $serialized = $this->toCamel($serialized);

        return array_merge($serialized, $this->attributes);
    }

    /**
     * Apply the registered decorators
     *
     * @param array $array
     *
     * @return array
     */
    private function applyDecorators(array $array)
    {
        foreach ($this->decorators as $decorator) {
            $array = $decorator->decorate($array);
        }

        return $array;
    }

    /**
     * Transform all keys to camelCase
     *
     * @param array $array
     *
     * @return array
     */
    private function toCamel(array $array)
    {
        $camelizedArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $camelizedArray[Str::camel($key)] = $this->toCamel($value);
            } else {
                $camelizedArray[Str::camel($key)] = $value;
            }
        }

        return $camelizedArray;
    }

    /**
     * Add an attribute
     *
     * @param array $attributes
     *
     * @return $this
     */
    public function addAttributes($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }
}