<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Shift
 *
 * @package App\Models
 */
class Shift extends BaseModel
{
    protected $dates = [
        'opened_at',
        'closed_at'
    ];
    protected $casts = [
        'opened_cash_balance' => 'float',
        'closed_cash_balance' => 'float'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function scopeInBranch(Builder $query, Branch $branch)
    {
        return $query->where('branch_id', '=', $branch->id);
    }

    public function scopeSuspended(Builder $query)
    {
        return $query->whereNull('closed_at')->where('opened_at', '<', Carbon::now()->startOfDay());
    }

    public function scopeOpen(Builder $query)
    {
        return $query->whereNull('closed_at')->whereBetween('opened_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
    }

    public function isClosed()
    {
        return $this->closed_at !== null;
    }

    public function isSuspended()
    {
        return $this->isClosed() === false && $this->opened_at->lt(Carbon::now()->startOfDay());
    }
}