<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PackageProduct
 *
 * @package App\Models
 */
class PackageProduct extends Model
{
    public $timestamps = false;

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}