<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class ProductCategoryPolicy
 *
 * @package App\Policies
 */
class ProductCategoryPolicy extends BasePolicy
{
    public function access(User $user)
    {
        return in_array($user->role, ['admin', 'tech_admin']);
    }
}
