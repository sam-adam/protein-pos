<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class ProductPolicy
 *
 * @package App\Policies
 */
class ProductPolicy extends BasePolicy
{
    public function update(User $user)
    {
        return in_array($user->role, ['admin', 'tech_admin']);
    }

    public function seeAllBranch(User $user)
    {
        return in_array($user->role, ['cashier', 'manager', 'admin', 'tech_admin']);
    }

    public function seeCost(User $user)
    {
        return $user->role !== 'cashier';
    }
}