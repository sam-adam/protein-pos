<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Branch
 *
 * @package App\Models
 */
class Branch extends Model
{
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function loginSessions()
    {
        return $this->hasMany(LoginSession::class);
    }

    public function currentlyLoggedInSessions()
    {
        return $this->loginSessions()->whereNull('logged_out_at');
    }
}