<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class SettingPolicy
 *
 * @package App\Policies
 */
class SettingPolicy extends BasePolicy
{
    public function access(User $user)
    {
        return in_array($user->role, ['admin', 'tech_admin']);
    }
}
