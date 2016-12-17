<?php

namespace App\Exceptions;

use App\Models\Inventory;

/**
 * Class InsufficientStockException
 *
 * @package App\Exceptions
 */
class InsufficientStockException extends \LogicException
{
    protected $inventory;

    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

    public function getInventory()
    {
        return $this->inventory;
    }
}