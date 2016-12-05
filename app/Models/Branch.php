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
}