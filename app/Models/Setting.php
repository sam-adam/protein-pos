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