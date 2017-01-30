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
        return true;
    }

    public function delete(User $user)
    {
        return $user->role !== 'cashier';
    }

    public function create(User $user)
    {
        return true;
    }

    public function assignToGroup(User $user)
    {
        return $user->role !== 'cashier';
    }
}
