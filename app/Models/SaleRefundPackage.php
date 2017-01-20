<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SaleRefundPackage
 *
 * @package App\Models
 */
class SaleRefundPackage extends Model
{
    public function salePackage()
    {
        return $this->hasOne(SalePackage::class);
    }
}