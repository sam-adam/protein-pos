<?php

namespace App\Models;

use App\Exceptions\InsufficientStockException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Inventory
 *
 * @package App\Models
 */
class Inventory extends BaseModel
{
    protected $dates = [
        'created_at',
        'updated_at',
        'expired_at',
        'expiry_reminder_date'
    ];

    /** {@inheritDoc} */
    protected static function boot()
    {
        parent::boot();

        self::saving(function (Inventory $inventory) {
            if ($inventory->stock < 0) {
                throw new InsufficientStockException($inventory);
            }
        });
    }

    public function scopeInBranch(Builder $query, Branch $branch)
    {
        return $query->where('branch_id', '=', $branch->id);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isExpired()
    {
        return $this->expired_at->lt(Carbon::now());
    }
}