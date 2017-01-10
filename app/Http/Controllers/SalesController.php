<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class SalesController
 *
 * @package App\Http\Controllers
 */
class SalesController extends AuthenticatedController
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $shift = Shift::inBranch($user->branch)->open()
            ->where('opened_by_user_id', $user->id)
            ->first();

        if (!$shift) {
            return redirect()->route('shifts.viewIn', ['redirect' => $request->fullUrl()])->with('flashes.error', 'Please clock in before making sales');
        }

        return view('sales.create');
    }
}