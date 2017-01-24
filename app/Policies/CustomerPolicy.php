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
        return in_array($user->role, ['manager', 'admin', 'tech_admin']);
    }
}
