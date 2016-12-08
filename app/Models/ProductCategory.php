<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class ProductCategory
 *
 * @package App\Models
 */
class ProductCategory extends BaseModel
{
    public function scopeRoots(Builder $query)
    {
        return $query->whereNull('parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}