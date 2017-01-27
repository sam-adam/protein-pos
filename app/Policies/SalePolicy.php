<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class SalePolicy
 *
 * @package App\Policies
 */
class SalePolicy extends BasePolicy
{
    public function giveWholeSaleDiscount(User $user)
    {
        return $user->role === 'admin';
    }

    public function create(User $user)
    {
        return $user->role !== 'tech_admin';
    }

    public function refund(User $user, Sale $sale)
    {
        if ($user->role === 'cashier') {
            return $user->can_do_refund && Carbon::now()->diffInMinutes($sale->closed_at) <= 24 * 60;
        } elseif ($user->role === 'manager') {
            return true;
        }

        return false;
    }

    public function modifyPrice(User $user)
    {
        return in_array($user->role, ['admin', 'manager']);
    }
}
