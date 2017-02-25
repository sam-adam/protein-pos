<?php

namespace App\Listeners;

use App\Models\LoginSession;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Session;

/**
 * Class LoginSessionLogger
 *
 * @package App\Listeners
 */
class LoginSessionLogger
{
    public function handle($event)
    {
        if ($event instanceof Login) {
            $this->handleLogin($event);
        } elseif ($event instanceof Logout) {
            $this->handleLogout($event);
        }
    }

    protected function handleLogin(Login $event)
    {
        $user = $event->user;

        if ($user->role === 'cashier') {
            $newLoginSession               = new LoginSession();
            $newLoginSession->user_id      = $user->id;
            $newLoginSession->branch_id    = $user->branch->id;
            $newLoginSession->logged_in_at = Carbon::now();
            $newLoginSession->saveOrFail();
        }
    }

    protected function handleLogout(Logout $event)
    {
        $user = $event->user;

        if ($user && $user->role === 'cashier') {
            $branch              = $user->branch;
            $currentLoginSession = $branch->currentlyLoggedInSessions()->where('user_id', '=', $user->id)->first();

            if ($currentLoginSession) {
                $currentLoginSession->logged_out_at = Carbon::now();
                $currentLoginSession->saveOrFail();
            }
        }
    }
}