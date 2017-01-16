<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SalePackageItem
 *
 * @package App\Models
 */
class SalePackageItem extends Model
{
    public $timestamps = false;

    public function salePackage()
    {
        return $this->belongsTo(SalePackage::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branchInventory()
    {
        return $this->belongsTo(Product::class);
    }
}