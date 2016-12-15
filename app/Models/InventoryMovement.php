<?php

namespace App\Models;

/**
 * Class InventoryMovement
 *
 * @package App\Models
 */
class InventoryMovement extends BaseModel
{
    public function from()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function to()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryMovementItem::class);
    }
}