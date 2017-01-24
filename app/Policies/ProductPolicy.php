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
    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'tech_admin']);
    }
}
