<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class ProductVariantGroupPolicy
 *
 * @package App\Policies
 */
class ProductVariantGroupPolicy extends BasePolicy
{
    public function access(User $user)
    {
        return in_array($user->role, ['admin', 'tech_admin']);
    }
}
