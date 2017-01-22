<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class BasePolicy
 *
 * @package App\Policies
 */
abstract class BasePolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return null;
    }
}