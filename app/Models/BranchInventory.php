<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

/**
 * Class BranchInventory
 *
 * @package App\Models
 */
class BranchInventory extends Model
{
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        self::saving(function (BranchInventory $branchInventory) {
            return $branchInventory->stock >= 0;
        });
    }

    public function scopeInBranch(Builder $query, Branch $branch)
    {
        return $query->where('branch_id', '=', $branch->id);
    }

    public function scopeProduct(Builder $query, Product $product)
    {
        return $query->select('branch_inventories.*')
            ->join('inventories', function (JoinClause $query) use ($product) {
                return $query->on('branch_inventories.inventory_id', '=' ,'inventories.id')
                    ->where('inventories.product_id', '=', $product->id);
            });
    }

    public function scopeNotEmpty(Builder $query)
    {
        return $query->where('stock', '>', 0);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function container()
    {
        return $this->belongsTo(BranchInventory::class, 'container_id');
    }
}