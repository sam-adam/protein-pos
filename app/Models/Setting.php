<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class Setting
 *
 * @package App\Models
 */
class Setting extends BaseModel
{
    const KEY_CREDIT_CARD_TAX      = 'credit_card_tax';
    const KEY_SALES_POINT_BASELINE = 'sales_point_baseline';
    const KEY_DELIVERY_PRODUCT_ID  = 'delivery_product_id';

    protected $fillable = ['key'];

    public function scopeKey(Builder $query, $key)
    {
        return $query->where('key', '=', $key);
    }

    public static function getValueByKey($key, $default = null)
    {
        $setting = self::key($key)->first();

        return $setting ? $setting->value : $default;
    }
}