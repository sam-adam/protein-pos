<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SaleRefundItem
 *
 * @package App\Models
 */
class SaleRefundItem extends Model
{
    public $timestamps = false;

    public function saleItem()
    {
        return $this->hasOne(SaleItem::class);
    }
}