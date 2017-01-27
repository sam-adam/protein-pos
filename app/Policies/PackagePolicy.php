<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class PackagePolicy
 *
 * @package App\Policies
 */
class PackagePolicy extends BasePolicy
{
    public function access(User $user)
    {
        return in_array($user->role, ['admin', 'tech_admin']);
    }
}
