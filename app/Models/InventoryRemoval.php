<?php

namespace App\Models;

/**
 * Class InventoryRemoval
 *
 * @package App\Models
 */
class InventoryRemoval extends BaseModel
{
    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }
}