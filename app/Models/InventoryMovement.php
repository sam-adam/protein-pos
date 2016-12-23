<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class InventoryMovement
 *
 * @package App\Models
 */
class InventoryMovement extends BaseModel
{
    protected $dates = ['movement_effective_at'];

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

    public function scopeFromBranch(Builder $query, Branch $fromBranch)
    {
        return $query->where('from_branch_id', '=', $fromBranch->id);
    }

    public function scopeToBranch(Builder $query, Branch $fromBranch)
    {
        return $query->where('to_branch_id', '=', $fromBranch->id);
    }

    public function scopeBranch(Builder $query, Branch $branch)
    {
        return $query->where(function ($query) use ($branch) {
            return $query->where('from_branch_id', '=', $branch->id)
                ->orWhere('to_branch_id', '=', $branch->id);
        });
    }
}