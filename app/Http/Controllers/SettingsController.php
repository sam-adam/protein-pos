<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettings;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Class SettingsController
 *
 * @package App\Http
 */
class SettingsController extends AuthenticatedController
{
    public function index(Request $request)
    {
        if ($redirectTo = $request->get('redirect-to')) {
            $request->session()->flash('redirect-to', $redirectTo);
        }

        return view('settings.index', [
            'creditCardTax'      => Setting::getValueByKey(Setting::KEY_CREDIT_CARD_TAX, 2),
            'salesPointBaseline' => Setting::getValueByKey(Setting::KEY_SALES_POINT_BASELINE, 0),
            'deliveryProductId'  => Setting::getValueByKey(Setting::KEY_DELIVERY_PRODUCT_ID),
            'walkInCustomerId'   => Setting::getValueByKey(Setting::KEY_WALK_IN_CUSTOMER_ID),
            'serviceProducts'    => Product::service()->get(),
            'customers'          => Customer::orderBy('name', 'asc')->get()
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

        $walkInCustomerId        = Setting::firstOrCreate(['key' => Setting::KEY_WALK_IN_CUSTOMER_ID]);
        $walkInCustomerId->value = $request->get('walk_in_customer_id');
        $walkInCustomerId->saveOrFail();

        $redirectTo = $request->session()->get('redirect-to');

        return redirect($redirectTo ?: route('settings.index'))->with('flashes.success', 'Settings updated');
    }
}