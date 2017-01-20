<?php

namespace App\Models;

/**
 * Class SaleRefund
 *
 * @package App\Models
 */
class SaleRefund extends BaseModel
{
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function items()
    {
        return $this->hasMany(SaleRefundItem::class);
    }

    public function packages()
    {
        return $this->hasMany(SaleRefundPackage::class);
    }
}