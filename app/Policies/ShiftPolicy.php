<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class ShiftPolicy
 *
 * @package App\Policies
 */
class ShiftPolicy extends BasePolicy
{
    public function access(User $user)
    {
        return in_array($user->role, ['admin', 'tech_admin']);
    }
}
