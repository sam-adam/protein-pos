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

    public function calculateSubTotal()
    {
        $subTotal = 0;

        foreach ($this->items as $item) {
            $subTotal += $item->calculateSubTotal();
        }

        return $subTotal;
    }

    public function calculateAfterCustomerDiscount()
    {
        $subTotal = $this->calculateSubTotal();

        if ($this->customer->group) {
            $subTotal = $subTotal * (100 - $this->customer->group->discount) / 100;
        }

        return $subTotal;
    }

    public function calculateAfterSalesDiscount()
    {
        $subTotal = $this->calculateAfterCustomerDiscount();
        $subTotal = $subTotal * (100 - $this->sales_discount) / 100;

        return $subTotal;
    }

    public function calculateTotal()
    {
        return $this->calculateAfterSalesDiscount();
    }
}