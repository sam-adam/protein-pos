<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class Sale
 *
 * @package App\Models
 */
class Sale extends BaseModel
{
    protected $casts = [
        'is_delivery' => 'boolean'
    ];
    protected $dates = [
        'opened_at', 'closed_at', 'cancelled_at', 'paid_at'
    ];

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function packages()
    {
        return $this->hasMany(SalePackage::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function refunds()
    {
        return $this->hasMany(SaleRefund::class);
    }

    public function scopeDelivery(Builder $query)
    {
        return $query->where('is_delivery', '=', true);
    }

    public function scopePaid(Builder $query)
    {
        return $query->whereNotNull('paid_at');
    }

    public function scopeFinished(Builder $query)
    {
        return $query->whereNotNull('closed_at');
    }

    public function scopeUnPaid(Builder $query)
    {
        return $query->whereNull('paid_at');
    }

    public function scopeCancelled(Builder $query)
    {
        return $query->whereNotNull('cancelled_at');
    }

    public function scopeNotCancelled(Builder $query)
    {
        return $query->whereNull('cancelled_at');
    }

    public function getType()
    {
        return $this->is_delivery ? 'Delivery' : 'Walk In';
    }

    public function isFinished()
    {
        return $this->closed_at !== null;
    }

    public function isCancelled()
    {
        return $this->cancelled_at !== null;
    }

    public function isPaid()
    {
        return $this->paid_at !== null;
    }

    public function getRefundableItems()
    {
        $refunds = $this->refunds;

        if ($refunds->count() === 0) {
            return $this->items;
        }

        $refundableItems = new Collection($this->items->all());

        foreach ($refundableItems as $refundableItem) {
            foreach ($refunds as $refund) {
                foreach ($refund->items as $refundedItem) {
                    if ((int) $refundedItem->sale_item_id === (int) $refundableItem->id) {
                        $refundableItem->quantity -= $refundedItem->quantity;
                    }
                }
            }
        }

        return $refundableItems->filter(function (SaleItem $saleItem) { return $saleItem->quantity > 0; });
    }

    public function getRefundablePackages()
    {
        $refunds = $this->refunds;

        if ($refunds->count() === 0) {
            return $this->packages;
        }

        $refundablePackages = new Collection($this->items->all());

        foreach ($refundablePackages as $refundablePackage) {
            foreach ($refunds as $refund) {
                foreach ($refund->packages as $refundedPackage) {
                    if ((int) $refundedPackage->sale_package_id === (int) $refundablePackage->id) {
                        $refundablePackage->quantity -= $refundedPackage->quantity;
                    }
                }
            }
        }

        return $refundablePackages->filter(function (SalePackage $salePackage) { return $salePackage->quantity > 0; });
    }

    public function isRefundable()
    {
        return $this->getRefundableItems() + $this->getRefundablePackages() > 0;
    }

    public function calculateSubTotal()
    {
        $subTotal = 0;

        /** @var SaleItem $item */
        foreach ($this->items as $item) {
            $subTotal += $item->calculateSubTotal();
        }

        /** @var SalePackage $package */
        foreach ($this->packages as $package) {
            $subTotal += $package->calculateSubTotal();
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