<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AuthenticatedController;
use App\Http\Requests\UpdateSettings;
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
            'creditCardTax' => Setting::getValueByKey('credit_card_tax', 2)
        ]);
    }

    public function update(UpdateSettings $request)
    {
        Setting::key('credit_card_tax')->update(['value' => $request->get('credit_card_tax')]);

        return redirect()->route('settings.index')->with('flashes.success', 'Settings updated');
    }
}