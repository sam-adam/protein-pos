<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Branch
 *
 * @package App\Models
 */
class Branch extends Model
{
    protected $dates = [
        'licensed_at',
        'activated_at'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function inventories()
    {
        return $this->hasMany(BranchInventory::class);
    }

    public function loginSessions()
    {
        return $this->hasMany(LoginSession::class);
    }

    public function currentlyLoggedInSessions()
    {
        return $this->loginSessions()->whereNull('logged_out_at');
    }

    public function scopeLicensed(Builder $query)
    {
        return $query->whereNotNull('licensed_at');
    }

    public function scopeActive(Builder $query)
    {
        return $query->whereNotNull('activated_at');
    }

    public function isLicensed()
    {
        return $this->licensed_at !== null;
    }

    public function isActive()
    {
        return $this->activated_at !== null;
    }
}