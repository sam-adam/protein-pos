<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettings;
use App\Models\Product;
use App\Models\Setting;

/**
 * Class SettingsController
 *
 * @package App\Http
 */
class SettingsController extends AuthenticatedController
{
    public function index()
    {
        return view('settings.index', [
            'creditCardTax'      => Setting::getValueByKey(Setting::KEY_CREDIT_CARD_TAX, 2),
            'salesPointBaseline' => Setting::getValueByKey(Setting::KEY_SALES_POINT_BASELINE, 0),
            'deliveryProductId'  => Setting::getValueByKey(Setting::KEY_DELIVERY_PRODUCT_ID),
            'serviceProducts'    => Product::service()->get()
        ]);
    }

    public function update(UpdateSettings $request)
    {
        $creditCardTax        = Setting::firstOrCreate(['key' => Setting::KEY_CREDIT_CARD_TAX]);
        $creditCardTax->value = $request->get('credit_card_tax');
        $creditCardTax->saveOrFail();

        $salesPointBaseline        = Setting::firstOrCreate(['key' => Setting::KEY_SALES_POINT_BASELINE]);
        $salesPointBaseline->value = $request->get('sales_point_baseline');
        $salesPointBaseline->saveOrFail();

        $deliveryProductId        = Setting::firstOrCreate(['key' => Setting::KEY_DELIVERY_PRODUCT_ID]);
        $deliveryProductId->value = $request->get('delivery_product_id');
        $deliveryProductId->saveOrFail();

        return redirect()->route('settings.index')->with('flashes.success', 'Settings updated');
    }
}