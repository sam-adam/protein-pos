<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LoginSession
 *
 * @package App\Models
 */
class LoginSession extends Model
{
    protected $dates = [
        'logged_in_at',
        'logged_out_at'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}