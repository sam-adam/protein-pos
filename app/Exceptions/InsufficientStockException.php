<?php

namespace App\Exceptions;

use App\Models\BranchInventory;

/**
 * Class InsufficientStockException
 *
 * @package App\Exceptions
 */
class InsufficientStockException extends \LogicException
{
    protected $inventory;

    public function __construct(BranchInventory $inventory)
    {
        $this->inventory = $inventory;
    }

    public function getInventory()
    {
        return $this->inventory;
    }
}