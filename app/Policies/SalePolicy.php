<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function before(User $user)
    {
        return $user->role === 'admin';
    }

    public function giveWholeSaleDiscount(User $user)
    {
        var_dump(User::find($user->id)->role);exit;
        return $user->role === 'admin';
    }
}
