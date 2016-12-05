<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Class IdleSessionMiddleware
 *
 * @package App\Http\Middleware
 */
class IdleSessionMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::user()) {
            $timeoutInMinutes = null;

            switch (Auth::user()->role) {
                case 'admin':
                    $timeoutInMinutes = 10;
                    break;
                case 'cashier':
                    $timeoutInMinutes = 20;
                    break;
            }

            if ($timeoutInMinutes) {
                $lastActivityTimestamp    = Carbon::createFromTimestamp(Session::get('last_activity_timestamp', Carbon::now()->timestamp));
                $currentActivityTimestamp = Carbon::now();
                $isSessionExpired         = false;

                if ($currentActivityTimestamp->diffInMinutes($lastActivityTimestamp) >= $timeoutInMinutes) {
                    $isSessionExpired = true;
                }

                Session::put('last_activity_timestamp', $currentActivityTimestamp->timestamp);

                if ($isSessionExpired) {
                    Session::flush();
                    Session::flash('flashes.error', 'Logged out because of idle activity');
                    Auth::logout();
                }
            }
        }

        return $next($request);
    }
}