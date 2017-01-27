<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class BrandPolicy
 *
 * @package App\Policies
 */
class BrandPolicy extends BasePolicy
{
    public function access(User $user)
    {
        return in_array($user->role, ['admin', 'tech_admin']);
    }
}
