<?php

namespace App\DTO;

/**
 * Class Collection
 *
 * @package App\DTO
 */
class Collection extends BaseDTO
{
    /** @var array */
    public $items;
    /** @var callable */
    private $transformer;
    /** @var bool */
    private $isTransformed = false;
    /** @var array */
    private $originalItems;
    /** @var bool */
    private $isFirstIteration = true;

    public function __construct($items = [], callable $transformer = null)
    {
        $this->items       = $items;
        $this->transformer = $transformer;
    }

    /** @param callable $callable */
    public function setTransformer(callable $callable)
    {
        $this->transformer = $callable;
    }

    /** {@inheritDoc} */
    public function toArray()
    {
        $parsed = [];

        foreach ($this->items as $key => $item) {
            if (!($item instanceof BaseDTO)) {
                if (!$this->isTransformed && is_callable($this->transformer)) {
                    $parsed[] = call_user_func_array($this->transformer, [$item]);
                } else {
                    $parsed[] = $item;
                }
            } else {
                $parsed[] = $item;
            }
        }

        $this->isTransformed = true;
        $this->originalItems = $this->items;
        $this->items         = $parsed;

        return parent::toArray();
    }

    protected function arrayHandler($value)
    {
        if ($this->isFirstIteration) {
            $this->isFirstIteration = false;

            return $value;
        }

        return parent::arrayHandler($value);
    }

}