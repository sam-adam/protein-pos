<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PackageVariant
 *
 * @package App\Models
 */
class PackageVariant extends Model
{
    public $timestamps = false;

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariantGroup::class, 'product_variant_group_id');
    }
}