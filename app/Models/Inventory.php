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

        self::saved(function (Inventory $inventory) {
            if ($inventory->isDirty('stock')) {
                //$availableInventories = Inventory::where('product_id', '=', $inventory->product_id)->orderBy()->get();
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branchItems()
    {
        return $this->hasMany(Product::class);
    }

    public function isExpired()
    {
        return $this->expired_at->lt(Carbon::now());
    }
}