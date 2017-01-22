<?php

namespace App\Policies;

use App\Models\User;

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
}
