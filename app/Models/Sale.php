<?php

namespace App\Models;

/**
 * Class Sale
 *
 * @package App\Models
 */
class Sale extends BaseModel
{
    protected $dates = [
        'opened_at', 'closed_at', 'cancelled_at', 'paid_at'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function isFinished()
    {
        return $this->closed_at !== null;
    }

    public function calculateTotal()
    {
        $total = 0;

        foreach ($this->items as $item) {
            $total += $item->calculateSubTotal();
        }

        if ($this->customer->group) {
            $total = $total * (100 - $this->customer->group->discount) / 100;
        }

        $total = $total * (100 - $this->sales_discount) / 100;

        return $total;
    }
}