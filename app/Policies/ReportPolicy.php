<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class ReportPolicy
 *
 * @package App\Policies
 */
class ReportPolicy extends BasePolicy
{
    public function access(User $user)
    {
        return $user->role !== 'cashier';
    }
}
