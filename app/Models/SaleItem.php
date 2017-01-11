<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SaleItem
 *
 * @package App\Models
 */
class SaleItem extends Model
{
    public $timestamps = false;

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branchInventory()
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateSubTotal()
    {
        return ($this->price * $this->quantity) * (100 - $this->discount) / 100;
    }
}