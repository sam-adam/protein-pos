<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Setting;

/**
 * Class PointService
 *
 * @package App\Services
 */
class PointService
{
    /**
     * Calculate points earned from a sales
     *
     * @param Sale $sale
     *
     * @return int
     */
    public function calculatePointsEarned(Sale $sale)
    {
        $threshold = Setting::getValueByKey(Setting::KEY_SALES_POINT_BASELINE, 0);

        if ($threshold === 0) {
            return 0;
        }

        if ($sale->payments->count() === 0) {
            return round(floor($sale->calculateTotal() / $threshold));
        } else {
            return round(floor($sale->payments->sum('amount') / $threshold));
        }
    }
}