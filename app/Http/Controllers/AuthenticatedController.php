<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\View;

/**
 * Class AuthenticatedController
 *
 * @package App\Http\Controllers
 */
abstract class AuthenticatedController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            $user = Auth::user();

            View::share('fullScreen', RequestFacade::get('external') || RequestFacade::get('fullscreen') || ($user && $user->role === 'cashier'));

            return $next($request);
        });

        if (!$this instanceof ShiftsController) {
            $this->middleware(function (Request $request, $next) {
                $user = Auth::user();

                if ($user && $user->role === 'cashier') {
                    $shift = Shift::inBranch($user->branch)->open()->where('opened_by_user_id', $user->id)->first();

                    if (!$shift) {
                        return redirect()->route('shifts.viewIn', ['redirect' => $request->fullUrl()])->with('flashes.error', 'Please clock in before making sales');
                    }
                }

                return $next($request);
            });
        }
    }
}