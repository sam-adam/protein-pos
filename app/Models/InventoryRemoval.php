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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productItem()
    {
        return $this->belongsTo(Product::class, 'product_item_id');
    }
}