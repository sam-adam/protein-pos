<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SalePackage
 *
 * @package App\Models
 */
class SalePackage extends Model
{
    public $timestamps = false;

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function items()
    {
        return $this->hasMany(SalePackageItem::class, 'sale_package_id');
    }

    public function calculateSubTotal()
    {
        return ($this->price * $this->quantity) * (100 - $this->discount) / 100;
    }
}