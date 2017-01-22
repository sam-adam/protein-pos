<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class BranchPolicy
 *
 * @package App\Policies
 */
class BranchPolicy extends BasePolicy
{
    public function access(User $user)
    {
        return $user->role === 'tech_admin';
    }
}
