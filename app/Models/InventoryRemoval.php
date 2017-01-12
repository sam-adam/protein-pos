<?php

namespace App\Models;

/**
 * Class InventoryRemoval
 *
 * @package App\Models
 */
class InventoryRemoval extends BaseModel
{
    public function branchInventory()
    {
        return $this->belongsTo(BranchInventory::class);
    }
}