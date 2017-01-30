<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class CustomerPolicy
 *
 * @package App\Policies
 */
class CustomerPolicy extends BasePolicy
{
    public function update(User $user)
    {
        return in_array($user->role, ['manager', 'admin', 'tech_admin']);
    }

    public function create(User $user)
    {
        return true;
    }

    public function assignToGroup(User $user)
    {
        return $this->update($user);
    }
}
