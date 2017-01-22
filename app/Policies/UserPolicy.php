<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class UserPolicy
 *
 * @package App\Policies
 */
class UserPolicy extends BasePolicy
{
    public function access(User $user)
    {
        return in_array($user->role, ['admin', 'tech_admin']);
    }
}
